<?php
require_once '../config.php';
$msg = '';

// ── ADD ──
if (isset($_POST['add'])) {
    $roll  = sanitize($conn, $_POST['roll_number']);
    $name  = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $pass  = md5($_POST['password']);
    $phone = sanitize($conn, $_POST['phone']);
    $r = mysqli_query($conn, "INSERT INTO students (roll_number,name,email,password,phone)
         VALUES ('$roll','$name','$email','$pass','$phone')");
    if ($r) {
        redirect('students.php?added=1');
    } else {
        $msg = '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
    }
}

// ── EDIT SAVE ──
if (isset($_POST['edit'])) {
    $id    = (int)$_POST['id'];
    $roll  = sanitize($conn, $_POST['roll_number']);
    $name  = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $phone = sanitize($conn, $_POST['phone']);
    if (!empty($_POST['password'])) {
        $pass = md5($_POST['password']);
        mysqli_query($conn, "UPDATE students SET roll_number='$roll',name='$name',email='$email',phone='$phone',password='$pass' WHERE id=$id");
    } else {
        mysqli_query($conn, "UPDATE students SET roll_number='$roll',name='$name',email='$email',phone='$phone' WHERE id=$id");
    }
    redirect('students.php?updated=1');
}

// ── DELETE ──
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM students WHERE id=".(int)$_GET['delete']);
    redirect('students.php');
}

// ── Edit mode: only on clean GET request ──
$edit = null;
if (isset($_GET['edit']) && !isset($_POST['edit']) && !isset($_POST['add'])) {
    $id   = (int)$_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id=$id"));
}

// Flash messages
if (isset($_GET['added']))   $msg = '<div class="alert alert-success">Student added successfully.</div>';
if (isset($_GET['updated'])) $msg = '<div class="alert alert-success">Student updated successfully.</div>';

$students = mysqli_query($conn, "SELECT * FROM students ORDER BY roll_number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Manage Students — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-user-graduate"></i> Students</div>
</div>
<?= $msg ?>

<div class="card">
    <h2><?= $edit ? '<i class="fas fa-edit"></i> Edit Student' : '<i class="fas fa-plus"></i> Add New Student' ?></h2>
    <form method="POST" action="students.php<?= $edit ? '?edit='.$edit['id'] : '' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Roll Number</label>
                <input name="roll_number" value="<?= htmlspecialchars($edit['roll_number'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($edit['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input name="phone" value="<?= htmlspecialchars($edit['phone'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Password <?= $edit ? '<span style="color:#888;font-weight:normal;">(leave blank to keep current)</span>' : '' ?></label>
            <input type="password" name="password" <?= !$edit ? 'required' : '' ?>>
        </div>
        <button type="submit" name="<?= $edit ? 'edit' : 'add' ?>" class="btn btn-primary">
            <?= $edit ? '💾 Update Student' : '<i class="fas fa-plus"></i> Add Student' ?>
        </button>
        <?php if ($edit): ?>
        <a href="students.php" class="btn btn-warning" style="margin-left:8px;">✕ Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>All Students (<?= mysqli_num_rows($students) ?>)</h2>
    <table>
        <tr><th>Roll No</th><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Actions</th></tr>
        <?php while ($s = mysqli_fetch_assoc($students)): ?>
        <tr>
            <td><?= htmlspecialchars($s['roll_number']) ?></td>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars($s['email']) ?></td>
            <td><?= $s['phone'] ?: '—' ?></td>
            <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
            <td>
                <a href="?edit=<?= $s['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                <a href="?delete=<?= $s['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete <?= htmlspecialchars(addslashes($s['name'])) ?>?')"><i class="fas fa-trash"></i> Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
