<?php
require_once '../config.php';
$msg = '';

if (isset($_POST['add'])) {
    $code    = sanitize($conn, $_POST['course_code']);
    $name    = sanitize($conn, $_POST['course_name']);
    $desc    = sanitize($conn, $_POST['description']);
    $credits = (int)$_POST['credits'];
    $fid     = (int)$_POST['faculty_id'];
    $fval    = $fid > 0 ? $fid : 'NULL';
    $r = mysqli_query($conn, "INSERT INTO courses (course_code,course_name,description,credits,faculty_id) VALUES ('$code','$name','$desc',$credits,$fval)");
    if ($r) redirect('courses.php?added=1');
    else $msg = '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
}
if (isset($_POST['edit'])) {
    $id      = (int)$_POST['id'];
    $code    = sanitize($conn, $_POST['course_code']);
    $name    = sanitize($conn, $_POST['course_name']);
    $desc    = sanitize($conn, $_POST['description']);
    $credits = (int)$_POST['credits'];
    $fid     = (int)$_POST['faculty_id'];
    $fval    = $fid > 0 ? $fid : 'NULL';
    mysqli_query($conn, "UPDATE courses SET course_code='$code',course_name='$name',description='$desc',credits=$credits,faculty_id=$fval WHERE id=$id");
    redirect('courses.php?updated=1');
}
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM courses WHERE id=".(int)$_GET['delete']);
    redirect('courses.php');
}

$edit = null;
if (isset($_GET['edit']) && !isset($_POST['edit']) && !isset($_POST['add'])) {
    $id   = (int)$_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM courses WHERE id=$id"));
}

if (isset($_GET['added']))   $msg = '<div class="alert alert-success">Course added successfully.</div>';
if (isset($_GET['updated'])) $msg = '<div class="alert alert-success">Course updated successfully.</div>';

$faculty_list = mysqli_query($conn, "SELECT * FROM faculty ORDER BY name");
$courses      = mysqli_query($conn, "SELECT c.*, f.name as faculty_name FROM courses c LEFT JOIN faculty f ON c.faculty_id=f.id ORDER BY c.course_code");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Manage Courses — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-book"></i> Courses</div>
</div>
<?= $msg ?>

<div class="card">
    <h2><?= $edit ? '<i class="fas fa-edit"></i> Edit Course' : '<i class="fas fa-plus"></i> Add Course' ?></h2>
    <form method="POST" action="courses.php<?= $edit ? '?edit='.$edit['id'] : '' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Course Code</label>
                <input name="course_code" value="<?= htmlspecialchars($edit['course_code'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Course Name</label>
                <input name="course_name" value="<?= htmlspecialchars($edit['course_name'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Instructor</label>
                <select name="faculty_id">
                    <option value="0">-- Unassigned --</option>
                    <?php mysqli_data_seek($faculty_list, 0); while ($f = mysqli_fetch_assoc($faculty_list)): ?>
                    <option value="<?= $f['id'] ?>" <?= (isset($edit['faculty_id']) && $edit['faculty_id']==$f['id']) ? 'selected':'' ?>>
                        <?= htmlspecialchars($f['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Credits</label>
                <input type="number" name="credits" value="<?= $edit['credits'] ?? 3 ?>" min="1" max="6">
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="2"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
        </div>
        <button type="submit" name="<?= $edit ? 'edit' : 'add' ?>" class="btn btn-primary">
            <?= $edit ? '💾 Update Course' : '<i class="fas fa-plus"></i> Add Course' ?>
        </button>
        <?php if ($edit): ?>
        <a href="courses.php" class="btn btn-warning" style="margin-left:8px;">✕ Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>All Courses (<?= mysqli_num_rows($courses) ?>)</h2>
    <table>
        <tr><th>Code</th><th>Name</th><th>Instructor</th><th>Credits</th><th>Actions</th></tr>
        <?php while ($c = mysqli_fetch_assoc($courses)): ?>
        <tr>
            <td><?= htmlspecialchars($c['course_code']) ?></td>
            <td><?= htmlspecialchars($c['course_name']) ?></td>
            <td><?= $c['faculty_name'] ? htmlspecialchars($c['faculty_name']) : '<span style="color:#aaa;">Unassigned</span>' ?></td>
            <td><?= $c['credits'] ?></td>
            <td>
                <a href="?edit=<?= $c['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                <a href="?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete course <?= htmlspecialchars(addslashes($c['course_code'])) ?>?')"><i class="fas fa-trash"></i> Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
