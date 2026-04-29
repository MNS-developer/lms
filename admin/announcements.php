<?php
require_once '../config.php';
$msg = '';

// CREATE TABLE IF NOT EXISTS
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    role ENUM('all','student','faculty') DEFAULT 'all',
    posted_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if (isset($_POST['add'])) {
    $title = sanitize($conn, $_POST['title']);
    $body  = sanitize($conn, $_POST['body']);
    $role  = sanitize($conn, $_POST['role']);
    $admin = $_SESSION['user_id'];
    mysqli_query($conn, "INSERT INTO announcements (title,body,role,posted_by) VALUES ('$title','$body','$role',$admin)");
    redirect('announcements.php?added=1');
}

if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM announcements WHERE id=".(int)$_GET['delete']);
    redirect('announcements.php');
}

if (isset($_GET['added'])) $msg = '<div class="alert alert-success">✅ Announcement posted!</div>';

$list = mysqli_query($conn, "SELECT a.*,ad.name as admin FROM announcements a LEFT JOIN admins ad ON a.posted_by=ad.id ORDER BY a.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Announcements — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-bullhorn"></i> Announcements</div>
</div>
<?= $msg ?>

<div class="card">
  <h2><i class="fas fa-plus"></i> Post New Announcement</h2>
  <form method="POST">
    <div class="form-row">
      <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" placeholder="Announcement title..." required>
      </div>
      <div class="form-group">
        <label>Audience</label>
        <select name="role">
          <option value="all">Everyone</option>
          <option value="student">Students Only</option>
          <option value="faculty">Faculty Only</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label>Message</label>
      <textarea name="body" rows="4" placeholder="Write your announcement..." required></textarea>
    </div>
    <button type="submit" name="add" class="btn btn-primary">📢 Post Announcement</button>
  </form>
</div>

<div class="card">
  <h2>📋 All Announcements (<?= mysqli_num_rows($list) ?>)</h2>
  <?php if (mysqli_num_rows($list)===0): ?>
    <p class="text-muted" style="padding:12px 0;">No announcements yet.</p>
  <?php else: ?>
  <?php while($a=mysqli_fetch_assoc($list)): ?>
  <div style="background:rgba(0,229,255,0.04);border:1px solid rgba(0,229,255,0.1);border-radius:12px;padding:16px 18px;margin-bottom:12px;position:relative;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
      <div>
        <div style="font-family:'Syne',sans-serif;font-weight:700;color:var(--text-primary);font-size:15px;margin-bottom:6px;"><?= htmlspecialchars($a['title']) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;line-height:1.6;"><?= nl2br(htmlspecialchars($a['body'])) ?></div>
        <div style="margin-top:10px;display:flex;gap:10px;align-items:center;">
          <span class="badge badge-info"><?= $a['role'] === 'all' ? '👥 Everyone' : ($a['role']==='student'?'🎓 Students':'👨‍🏫 Faculty') ?></span>
          <span class="text-muted" style="font-size:11px;">Posted by <?= htmlspecialchars($a['admin']??'Admin') ?> · <?= date('d M Y, H:i',strtotime($a['created_at'])) ?></span>
        </div>
      </div>
      <a href="?delete=<?= $a['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this announcement?')"><i class="fas fa-trash"></i> </a>
    </div>
  </div>
  <?php endwhile; ?>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
