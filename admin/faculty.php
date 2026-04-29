<?php
require_once '../config.php';
$msg = '';

// ── ADD ──
if (isset($_POST['add'])) {
    $name  = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $pass  = md5($_POST['password']);
    $dept  = sanitize($conn, $_POST['department']);
    $phone = sanitize($conn, $_POST['phone']);
    $r = mysqli_query($conn, "INSERT INTO faculty (name,email,password,department,phone)
         VALUES ('$name','$email','$pass','$dept','$phone')");
    if ($r) {
        redirect('faculty.php?added=1'); // redirect to clean URL = form resets to Add mode
    } else {
        $msg = '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
    }
}

// ── EDIT SAVE ──
if (isset($_POST['edit'])) {
    $id    = (int)$_POST['id'];
    $name  = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $dept  = sanitize($conn, $_POST['department']);
    $phone = sanitize($conn, $_POST['phone']);
    if (!empty($_POST['password'])) {
        $pass = md5($_POST['password']);
        mysqli_query($conn, "UPDATE faculty SET name='$name',email='$email',department='$dept',phone='$phone',password='$pass' WHERE id=$id");
    } else {
        mysqli_query($conn, "UPDATE faculty SET name='$name',email='$email',department='$dept',phone='$phone' WHERE id=$id");
    }
    redirect('faculty.php?updated=1'); // redirect strips ?edit=X — form resets to Add mode
}

// ── DELETE ──
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM faculty WHERE id=".(int)$_GET['delete']);
    redirect('faculty.php');
}

// ── Edit mode: only when ?edit=X is present and NO POST is happening ──
$edit = null;
if (isset($_GET['edit']) && !isset($_POST['edit']) && !isset($_POST['add'])) {
    $id   = (int)$_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM faculty WHERE id=$id"));
}

// Flash messages from redirect
if (isset($_GET['added']))   $msg = '<div class="alert alert-success">Faculty added successfully.</div>';
if (isset($_GET['updated'])) $msg = '<div class="alert alert-success">Faculty updated successfully.</div>';

$list = mysqli_query($conn, "SELECT * FROM faculty ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Manage Faculty — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-chalkboard-teacher"></i> Faculty</div>
</div>
<?= $msg ?>

<div class="card">
    <h2><?= $edit ? '<i class="fas fa-edit"></i> Edit Faculty' : '<i class="fas fa-plus"></i> Add Faculty' ?></h2>
    <form method="POST" action="faculty.php<?= $edit ? '?edit='.$edit['id'] : '' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Full Name</label>
                <input name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required <?= !$edit ? 'autofocus' : '' ?>>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($edit['email'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Department</label>
                <input name="department" value="<?= htmlspecialchars($edit['department'] ?? '') ?>">
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
            <?= $edit ? '💾 Update Faculty' : '<i class="fas fa-plus"></i> Add Faculty' ?>
        </button>
        <?php if ($edit): ?>
        <a href="faculty.php" class="btn btn-warning" style="margin-left:8px;">✕ Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>All Faculty (<?= mysqli_num_rows($list) ?>)</h2>
    <table>
        <tr><th>Name</th><th>Email</th><th>Department</th><th>Phone</th><th>Actions</th></tr>
        <?php while ($f = mysqli_fetch_assoc($list)): ?>
        <tr>
            <td><?= htmlspecialchars($f['name']) ?></td>
            <td><?= htmlspecialchars($f['email']) ?></td>
            <td><?= $f['department'] ?: '—' ?></td>
            <td><?= $f['phone'] ?: '—' ?></td>
            <td>
                <a href="?edit=<?= $f['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                <a href="?delete=<?= $f['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete <?= htmlspecialchars(addslashes($f['name'])) ?>?')"><i class="fas fa-trash"></i> Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
