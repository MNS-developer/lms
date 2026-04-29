<?php
require_once '../config.php';
$fid = $_SESSION['user_id'];
$msg = '';
if (isset($_GET['updated'])) $msg = '<div class="alert alert-success">Assessment updated.</div>';

// ── ADD ──
if (isset($_POST['add'])) {
    $cid      = (int)$_POST['course_id'];
    $title    = sanitize($conn, $_POST['title']);
    $type     = sanitize($conn, $_POST['type']);
    $desc     = sanitize($conn, $_POST['description']);
    $deadline = sanitize($conn, $_POST['deadline']);
    $marks    = (int)$_POST['total_marks'];
    $dl_val   = $deadline ? "'$deadline'" : 'NULL';
    $check    = mysqli_fetch_row(mysqli_query($conn, "SELECT id FROM courses WHERE id=$cid AND faculty_id=$fid"));
    if ($check) {
        mysqli_query($conn, "INSERT INTO assessments (course_id,title,type,description,deadline,total_marks)
            VALUES ($cid,'$title','$type','$desc',$dl_val,$marks)");
        $msg = '<div class="alert alert-success">Assessment created.</div>';
    }
}

// ── EDIT SAVE ──
if (isset($_POST['edit'])) {
    $id       = (int)$_POST['id'];
    $cid      = (int)$_POST['course_id'];
    $title    = sanitize($conn, $_POST['title']);
    $type     = sanitize($conn, $_POST['type']);
    $desc     = sanitize($conn, $_POST['description']);
    $deadline = sanitize($conn, $_POST['deadline']);
    $marks    = (int)$_POST['total_marks'];
    $dl_val   = $deadline ? "'$deadline'" : 'NULL';
    mysqli_query($conn, "UPDATE assessments SET course_id=$cid,title='$title',type='$type',
        description='$desc',deadline=$dl_val,total_marks=$marks
        WHERE id=$id AND course_id IN (SELECT id FROM courses WHERE faculty_id=$fid)");
    $msg = '<div class="alert alert-success">Assessment updated.</div>';
    redirect('assessments.php?updated=1');
}

// ── DELETE ──
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM assessments WHERE id=$id
        AND course_id IN (SELECT id FROM courses WHERE faculty_id=$fid)");
    redirect('assessments.php?updated=1');
}

$edit_id  = isset($_GET['edit']) && !isset($_POST['edit']) && !isset($_POST['add']) ? (int)$_GET['edit'] : 0;
$edit_row = $edit_id ? mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.* FROM assessments a JOIN courses c ON a.course_id=c.id
     WHERE a.id=$edit_id AND c.faculty_id=$fid")) : null;

$courses       = mysqli_query($conn, "SELECT * FROM courses WHERE faculty_id=$fid ORDER BY course_code");
$course_filter = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$where         = $course_filter ? "AND a.course_id=$course_filter" : '';
$assessments   = mysqli_query($conn, "SELECT a.*, c.course_code, c.course_name,
    (SELECT COUNT(*) FROM submissions s WHERE s.assessment_id=a.id) as sub_count
    FROM assessments a JOIN courses c ON a.course_id=c.id
    WHERE c.faculty_id=$fid $where ORDER BY a.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Assessments — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-tasks"></i> Assessments</div>
</div>
<?= $msg ?>

<div class="card">
    <h2><?= $edit_row ? 'Edit Assessment' : 'Create Assessment' ?></h2>
    <form method="POST">
        <?php if ($edit_row): ?><input type="hidden" name="id" value="<?= $edit_row['id'] ?>"><?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Course</label>
                <select name="course_id" required>
                    <option value="">-- Select --</option>
                    <?php mysqli_data_seek($courses,0); while ($c = mysqli_fetch_assoc($courses)): ?>
                    <option value="<?= $c['id'] ?>"
                        <?= ($edit_row && $edit_row['course_id']==$c['id']) || $course_filter==$c['id'] ? 'selected':'' ?>>
                        <?= htmlspecialchars($c['course_code']) ?> - <?= htmlspecialchars($c['course_name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="type">
                    <?php foreach(['assignment','quiz','project'] as $t): ?>
                    <option <?= ($edit_row && $edit_row['type']===$t)?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Title</label>
                <input name="title" value="<?= htmlspecialchars($edit_row['title'] ?? '') ?>" required>
            </div>
            <div class="form-group"><label>Total Marks</label>
                <input type="number" name="total_marks" value="<?= $edit_row['total_marks'] ?? 100 ?>" min="1">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Deadline</label>
                <input type="datetime-local" name="deadline"
                    value="<?= $edit_row && $edit_row['deadline'] ? date('Y-m-d\TH:i', strtotime($edit_row['deadline'])) : '' ?>">
            </div>
        </div>
        <div class="form-group"><label>Description</label>
            <textarea name="description" rows="2"><?= htmlspecialchars($edit_row['description'] ?? '') ?></textarea>
        </div>
        <button type="submit" name="<?= $edit_row ? 'edit' : 'add' ?>" class="btn btn-primary">
            <?= $edit_row ? 'Update Assessment' : 'Create Assessment' ?>
        </button>
        <?php if ($edit_row): ?>
        <a href="assessments.php" class="btn btn-warning" style="margin-left:8px;">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>Assessments List</h2>
    <table>
        <tr><th>Course</th><th>Title</th><th>Type</th><th>Marks</th><th>Deadline</th><th>Submissions</th><th>Actions</th></tr>
        <?php while ($a = mysqli_fetch_assoc($assessments)): ?>
        <tr>
            <td><?= htmlspecialchars($a['course_code']) ?></td>
            <td><?= htmlspecialchars($a['title']) ?></td>
            <td><span class="badge badge-info"><?= $a['type'] ?></span></td>
            <td><?= $a['total_marks'] ?></td>
            <td><?= $a['deadline'] ? date('d M Y H:i', strtotime($a['deadline'])) : '<span style="color:#aaa;">None</span>' ?></td>
            <td><?= $a['sub_count'] ?></td>
            <td>
                <a href="submissions.php?assessment_id=<?= $a['id'] ?>" class="btn btn-info btn-sm">Submissions</a>
                <a href="?edit=<?= $a['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="?delete=<?= $a['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this assessment?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
