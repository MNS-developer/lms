<?php
require_once '../config.php';
$sid = $_SESSION['user_id'];
$msg = '';

if (isset($_POST['update'])) {
    $name  = sanitize($conn, $_POST['name']);
    $phone = sanitize($conn, $_POST['phone']);
    $addr  = sanitize($conn, $_POST['address']);
    $sql   = "UPDATE students SET name='$name',phone='$phone',address='$addr' WHERE id=$sid";

    if (!empty($_POST['new_password'])) {
        $old = md5($_POST['old_password']);
        $chk = mysqli_fetch_row(mysqli_query($conn,"SELECT id FROM students WHERE id=$sid AND password='$old'"));
        if (!$chk) {
            $msg = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Old password is incorrect.</div>';
        } else {
            $new = md5($_POST['new_password']);
            $sql = "UPDATE students SET name='$name',phone='$phone',address='$addr',password='$new' WHERE id=$sid";
        }
    }
    if (!$msg) {
        mysqli_query($conn, $sql);
        $_SESSION['user_name'] = $name;
        $msg = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Profile updated successfully.</div>';
    }
}

$s = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM students WHERE id=$sid"));
$initials = strtoupper(substr($s['name'],0,1));

// counts
$enrolled = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM enrollments WHERE student_id=$sid AND status='approved'"))[0];
$graded   = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM grades g JOIN submissions s ON g.submission_id=s.id WHERE s.student_id=$sid"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Profile — Learnix</title>
</head>
<body>
<?php include 'header.php'; ?>

<div class="page-header">
  <div class="page-title"><i class="fas fa-user-circle"></i> My Profile</div>
</div>

<?= $msg ?>

<div class="profile-grid">

  <!-- Left: Profile Card -->
  <div>
    <div class="profile-card">
      <div class="avatar"><?= $initials ?></div>
      <div class="profile-name"><?= htmlspecialchars($s['name']) ?></div>
      <div class="profile-role">Student</div>

      <div style="margin-top:20px;">
        <div class="profile-info-row">
          <i class="fas fa-id-badge"></i>
          <div>
            <div class="profile-info-label">Roll Number</div>
            <div class="profile-info-val"><?= htmlspecialchars($s['roll_number']) ?></div>
          </div>
        </div>
        <div class="profile-info-row">
          <i class="fas fa-envelope"></i>
          <div>
            <div class="profile-info-label">Email</div>
            <div class="profile-info-val" style="word-break:break-all;"><?= htmlspecialchars($s['email']) ?></div>
          </div>
        </div>
        <div class="profile-info-row">
          <i class="fas fa-phone"></i>
          <div>
            <div class="profile-info-label">Phone</div>
            <div class="profile-info-val"><?= htmlspecialchars($s['phone'] ?: '—') ?></div>
          </div>
        </div>
        <div class="profile-info-row">
          <i class="fas fa-map-marker-alt"></i>
          <div>
            <div class="profile-info-label">Address</div>
            <div class="profile-info-val"><?= htmlspecialchars($s['address'] ?: '—') ?></div>
          </div>
        </div>
        <div class="profile-info-row">
          <i class="fas fa-calendar-alt"></i>
          <div>
            <div class="profile-info-label">Member Since</div>
            <div class="profile-info-val"><?= date('d M Y', strtotime($s['created_at'])) ?></div>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:20px;padding-top:18px;border-top:1px solid var(--border);">
        <div style="text-align:center;padding:12px;background:var(--primary-light);border-radius:var(--radius);">
          <div style="font-size:22px;font-weight:800;color:var(--primary);"><?= $enrolled ?></div>
          <div style="font-size:11px;color:var(--text-3);margin-top:2px;">Courses</div>
        </div>
        <div style="text-align:center;padding:12px;background:var(--success-light);border-radius:var(--radius);">
          <div style="font-size:22px;font-weight:800;color:var(--success);"><?= $graded ?></div>
          <div style="font-size:11px;color:var(--text-3);margin-top:2px;">Graded</div>
        </div>
      </div>

      <!-- Quick links -->
      <div style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
        <a href="transcript.php" class="btn btn-outline w-full"><i class="fas fa-file-alt"></i> View Transcript</a>
        <a href="rollslip.php"   class="btn btn-secondary w-full"><i class="fas fa-id-card"></i> Roll Number Slip</a>
      </div>
    </div>
  </div>

  <!-- Right: Edit Forms -->
  <div>
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fas fa-edit"></i> Edit Personal Information</div>
      </div>
      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($s['name']) ?>" required>
          </div>
          <div class="form-group">
            <label>Roll Number</label>
            <input type="text" value="<?= htmlspecialchars($s['roll_number']) ?>" disabled>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" value="<?= htmlspecialchars($s['email']) ?>" disabled>
            <div class="form-hint">Email cannot be changed. Contact admin.</div>
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($s['phone'] ?: '') ?>" placeholder="e.g. 0300-1234567">
          </div>
        </div>
        <div class="form-group">
          <label>Home Address</label>
          <textarea name="address" rows="2" placeholder="Your address..."><?= htmlspecialchars($s['address'] ?: '') ?></textarea>
        </div>

        <hr class="divider">
        <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-lock"></i> Change Password <span style="font-size:12px;font-weight:400;color:var(--text-3);">(leave blank to keep current)</span></div>
        <div class="form-row">
          <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="old_password" placeholder="Enter current password">
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Enter new password">
          </div>
        </div>

        <button type="submit" name="update" class="btn btn-primary">
          <i class="fas fa-save"></i> Save Changes
        </button>
      </form>
    </div>
  </div>

</div><!-- profile-grid -->

<?php include 'footer.php'; ?>
</body>
</html>
