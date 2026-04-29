<?php
require_once '../config.php';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard — Learnix</title>
</head>
<body>
<?php include 'header.php'; $sid = $_SESSION['user_id']; ?>

<div class="page-header">
  <div class="page-title"><i class="fas fa-home"></i> Dashboard</div>
</div>

<?php
$enrolled    = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM enrollments WHERE student_id=$sid AND status='approved'"))[0];
$pending_enr = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM enrollments WHERE student_id=$sid AND status='pending'"))[0];
$unsubmitted = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM assessments a
    JOIN enrollments e ON a.course_id=e.course_id
    LEFT JOIN submissions s ON a.id=s.assessment_id AND s.student_id=$sid
    WHERE e.student_id=$sid AND e.status='approved' AND s.id IS NULL
    AND (a.deadline IS NULL OR a.deadline > NOW())"))[0];
$graded = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM grades g JOIN submissions s ON g.submission_id=s.id WHERE s.student_id=$sid"))[0];
?>

<div class="stats">
  <div class="stat-box">
    <div class="stat-icon"><i class="fas fa-book-open"></i></div>
    <div class="num"><?= $enrolled ?></div>
    <div class="label">Active Courses</div>
  </div>
  <div class="stat-box orange">
    <div class="stat-icon"><i class="fas fa-clock"></i></div>
    <div class="num"><?= $pending_enr ?></div>
    <div class="label">Pending Enrollments</div>
  </div>
  <div class="stat-box red">
    <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <div class="num"><?= $unsubmitted ?></div>
    <div class="label">Pending Submissions</div>
  </div>
  <div class="stat-box green">
    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
    <div class="num"><?= $graded ?></div>
    <div class="label">Graded Items</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;">
<div>

<!-- Enrolled Courses -->
<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fas fa-book-open"></i> My Enrolled Courses</div>
    <a href="register.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Register</a>
  </div>
  <?php
  $courses = mysqli_query($conn,"SELECT c.*, f.name as faculty_name
      FROM courses c JOIN enrollments e ON c.id=e.course_id
      LEFT JOIN faculty f ON c.faculty_id=f.id
      WHERE e.student_id=$sid AND e.status='approved' ORDER BY c.course_code");
  ?>
  <?php if (!$courses || mysqli_num_rows($courses)===0): ?>
    <p class="text-muted">No enrolled courses yet. <a href="register.php">Register for a course</a></p>
  <?php else: ?>
  <div class="table-wrap">
  <table>
    <thead><tr><th>Code</th><th>Course</th><th>Instructor</th><th>Credits</th><th>Actions</th></tr></thead>
    <tbody>
    <?php while ($c=mysqli_fetch_assoc($courses)): ?>
    <tr>
      <td><span class="badge badge-info"><?= htmlspecialchars($c['course_code']) ?></span></td>
      <td><strong><?= htmlspecialchars($c['course_name']) ?></strong></td>
      <td class="text-muted"><?= $c['faculty_name'] ? htmlspecialchars($c['faculty_name']) : 'N/A' ?></td>
      <td><?= $c['credits'] ?> cr</td>
      <td style="white-space:nowrap;">
        <a href="attendance_view.php?course_id=<?= $c['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-chart-pie"></i> Attendance</a>
        <a href="assessments_view.php?course_id=<?= $c['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-tasks"></i> Assessments</a>
        <a href="materials_view.php?course_id=<?= $c['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-file-download"></i> Materials</a>
      </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>

<!-- Upcoming Deadlines -->
<div class="card">
  <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-calendar-check"></i> Upcoming Deadlines</div>
  <?php
  $deadlines = mysqli_query($conn,"SELECT a.title,a.type,a.deadline,c.course_name,c.course_code,
      (SELECT COUNT(*) FROM submissions WHERE assessment_id=a.id AND student_id=$sid) as submitted
      FROM assessments a JOIN courses c ON a.course_id=c.id
      JOIN enrollments e ON e.course_id=c.id AND e.student_id=$sid AND e.status='approved'
      WHERE a.deadline > NOW() ORDER BY a.deadline ASC LIMIT 6");
  ?>
  <?php if (!$deadlines||mysqli_num_rows($deadlines)===0): ?>
    <p class="text-muted"><i class="fas fa-check-circle" style="color:var(--success)"></i> No upcoming deadlines.</p>
  <?php else: ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Assessment</th><th>Course</th><th>Deadline</th><th>Status</th></tr></thead>
    <tbody>
    <?php while($d=mysqli_fetch_assoc($deadlines)):
      $days = ceil((strtotime($d['deadline'])-time())/86400);
      $urgency = $days<=2?'var(--danger)':($days<=6?'var(--warning)':'var(--text-2)');
    ?>
    <tr>
      <td><strong><?= htmlspecialchars($d['title']) ?></strong><br><small class="text-muted"><?= $d['type'] ?></small></td>
      <td><span class="badge badge-gray"><?= htmlspecialchars($d['course_code']) ?></span></td>
      <td style="color:<?= $urgency ?>;font-size:12px;"><?= date('d M, H:i',strtotime($d['deadline'])) ?><br><small><?= $days ?>d left</small></td>
      <td><?= $d['submitted'] ? '<span class="badge badge-success"><i class="fas fa-check"></i> Done</span>' : '<span class="badge badge-danger">Pending</span>' ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>

</div><!-- left col -->

<!-- Right: Announcements -->
<div>
<div class="card">
  <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-bullhorn"></i> Announcements</div>
  <?php
  // ensure table exists
  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS announcements (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, body TEXT NOT NULL, role ENUM('all','student','faculty') DEFAULT 'all', posted_by INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
  $ann = mysqli_query($conn,"SELECT * FROM announcements WHERE role IN ('all','student') ORDER BY created_at DESC LIMIT 8");
  ?>
  <?php if (!$ann||mysqli_num_rows($ann)===0): ?>
    <p class="text-muted" style="font-size:13px;">No announcements yet.</p>
  <?php else: ?>
  <?php while($a=mysqli_fetch_assoc($ann)): ?>
  <div class="announcement-item">
    <div class="announcement-icon"><i class="fas fa-megaphone"></i></div>
    <div>
      <div class="announcement-title"><?= htmlspecialchars($a['title']) ?></div>
      <div class="announcement-body"><?= nl2br(htmlspecialchars($a['body'])) ?></div>
      <div class="announcement-meta"><i class="far fa-clock"></i> <?= date('d M Y',strtotime($a['created_at'])) ?></div>
    </div>
  </div>
  <?php endwhile; ?>
  <?php endif; ?>
</div>
</div><!-- right col -->

</div><!-- grid -->

<?php include 'footer.php'; ?>
</body></html>
