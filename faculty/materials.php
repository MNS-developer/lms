<?php
require_once '../config.php';
$fid = $_SESSION['user_id'];
$msg = '';

// ── EDIT title — must come BEFORE $edit_id is read ──
if (isset($_POST['edit_title'])) {
    $id    = (int)$_POST['id'];
    $title = sanitize($conn, $_POST['title']);
    mysqli_query($conn, "UPDATE materials m JOIN courses c ON m.course_id=c.id
        SET m.title='$title' WHERE m.id=$id AND c.faculty_id=$fid");
    redirect('materials.php'); // redirect clears GET params — form shows empty/add mode
}

// ── UPLOAD ──
if (isset($_POST['upload'])) {
    $cid   = (int)$_POST['course_id'];
    $title = sanitize($conn, $_POST['title']);
    $check = mysqli_fetch_row(mysqli_query($conn, "SELECT id FROM courses WHERE id=$cid AND faculty_id=$fid"));
    if (!$check) {
        $msg = '<div class="alert alert-danger">Invalid course.</div>';
    } else {
        $error    = '';
        $filename = handle_upload('file', UPLOAD_MATERIALS_PATH, $error);
        if ($filename) {
            mysqli_query($conn, "INSERT INTO materials (course_id,title,filename) VALUES ($cid,'$title','$filename')");
            $msg = '<div class="alert alert-success">✅ Material uploaded successfully.</div>';
        } else {
            $msg = '<div class="alert alert-danger">Upload failed: ' . $error . '</div>';
        }
    }
}

// ── DELETE ──
if (isset($_GET['delete'])) {
    $id  = (int)$_GET['delete'];
    $mat = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT m.* FROM materials m JOIN courses c ON m.course_id=c.id WHERE m.id=$id AND c.faculty_id=$fid"));
    if ($mat) {
        @unlink(UPLOAD_MATERIALS_PATH . $mat['filename']);
        mysqli_query($conn, "DELETE FROM materials WHERE id=$id");
    }
    redirect('materials.php');
}

// ── Edit mode (GET only, never during POST) ──
$edit_id  = (!isset($_POST) || count($_POST) === 0) && isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_mat = $edit_id ? mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT m.* FROM materials m JOIN courses c ON m.course_id=c.id WHERE m.id=$edit_id AND c.faculty_id=$fid")) : null;

$courses   = mysqli_query($conn, "SELECT * FROM courses WHERE faculty_id=$fid ORDER BY course_code");
$materials = mysqli_query($conn, "SELECT m.*, c.course_code, c.course_name FROM materials m
    JOIN courses c ON m.course_id=c.id WHERE c.faculty_id=$fid ORDER BY m.uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Study Materials — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-folder-open"></i> Study Materials</div>
</div>
<?= $msg ?>

<?php if ($edit_mat): ?>
<div class="card" style="border-top:3px solid #e67e22;">
    <h2><i class="fas fa-edit"></i> Edit Material Title</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit_mat['id'] ?>">
        <div class="form-row">
            <div class="form-group" style="flex:2;">
                <label>Title</label>
                <input name="title" value="<?= htmlspecialchars($edit_mat['title']) ?>" required autofocus>
            </div>
        </div>
        <button type="submit" name="edit_title" class="btn btn-primary">💾 Save Title</button>
        <a href="materials.php" class="btn btn-warning" style="margin-left:8px;">Cancel</a>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <h2><i class="fas fa-plus"></i> Upload New Material</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Course</label>
                <select name="course_id" required>
                    <option value="">-- Select --</option>
                    <?php mysqli_data_seek($courses, 0); while ($c = mysqli_fetch_assoc($courses)): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_code']) ?> – <?= htmlspecialchars($c['course_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Title</label>
                <input name="title" placeholder="e.g. Week 3 Lecture Notes" required>
            </div>
        </div>
        <div class="form-group">
            <label>File <span style="color:#888;font-weight:normal;">(PDF, DOC, DOCX, PPT, PPTX, TXT, ZIP — max 10MB)</span></label>
            <input type="file" name="file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip">
        </div>
        <button type="submit" name="upload" class="btn btn-primary">⬆ Upload</button>
    </form>
</div>

<div class="card">
    <h2>Uploaded Materials</h2>
    <table>
        <tr><th>Course</th><th>Title</th><th>Uploaded</th><th>Actions</th></tr>
        <?php $count = 0; while ($m = mysqli_fetch_assoc($materials)): $count++; ?>
        <tr>
            <td><?= htmlspecialchars($m['course_code']) ?></td>
            <td><?= htmlspecialchars($m['title']) ?></td>
            <td><?= date('d M Y', strtotime($m['uploaded_at'])) ?></td>
            <td>
                <a href="<?= LMS_BASE ?>/download.php?type=material&file=<?= rawurlencode($m['filename']) ?>"
                   class="btn btn-info btn-sm">⬇ Download</a>
                <a href="?edit=<?= $m['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete this material?')"><i class="fas fa-trash"></i> Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if ($count === 0): ?>
        <tr><td colspan="4" style="text-align:center;color:#aaa;padding:20px;">No materials uploaded yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
