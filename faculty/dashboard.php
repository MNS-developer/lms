<?php require_once '../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Faculty Dashboard — Learnix</title>
</head>
<body>
<?php include 'header.php'; $fid=$_SESSION['user_id']; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-home"></i> Dashboard</div>
</div>
<?php
$courses_cnt = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM courses WHERE faculty_id=$fid"))[0];
$students_cnt= mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(DISTINCT e.student_id) FROM enrollments e JOIN courses c ON e.course_id=c.id WHERE c.faculty_id=$fid AND e.status='approved'"))[0];
$assess_cnt  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM assessments a JOIN courses c ON a.course_id=c.id WHERE c.faculty_id=$fid"))[0];
$ungraded    = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM submissions s JOIN assessments a ON s.assessment_id=a.id JOIN courses c ON a.course_id=c.id LEFT JOIN grades g ON s.id=g.submission_id WHERE c.faculty_id=$fid AND g.id IS NULL"))[0];
?>
<div class="stats">
  <div class="stat-box"><div class="stat-icon"><i class="fas fa-book"></i></div><div class="num"><?=$courses_cnt?></div><div class="label">My Courses</div></div>
  <div class="stat-box green"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="num"><?=$students_cnt?></div><div class="label">Total Students</div></div>
  <div class="stat-box orange"><div class="stat-icon"><i class="fas fa-tasks"></i></div><div class="num"><?=$assess_cnt?></div><div class="label">Assessments</div></div>
  <div class="stat-box red"><div class="stat-icon"><i class="fas fa-inbox"></i></div><div class="num"><?=$ungraded?></div><div class="label">To Grade</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;">
<div>
<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fas fa-book"></i> My Courses</div>
    <a href="courses.php" class="btn btn-secondary btn-sm">View All</a>
  </div>
  <?php $courses=mysqli_query($conn,"SELECT c.*,(SELECT COUNT(*) FROM enrollments e WHERE e.course_id=c.id AND e.status='approved') as enrolled FROM courses c WHERE c.faculty_id=$fid ORDER BY c.course_code"); ?>
  <?php if(mysqli_num_rows($courses)===0): ?>
    <p class="text-muted">No courses assigned. Contact admin.</p>
  <?php else: ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Code</th><th>Course</th><th>Students</th><th>Actions</th></tr></thead>
    <tbody>
    <?php while($c=mysqli_fetch_assoc($courses)): ?>
    <tr>
      <td><span class="badge badge-info"><?=htmlspecialchars($c['course_code'])?></span></td>
      <td><strong><?=htmlspecialchars($c['course_name'])?></strong><br><small class="text-muted"><?=$c['credits']?> credits</small></td>
      <td><?=$c['enrolled']?></td>
      <td style="white-space:nowrap;">
        <a href="attendance.php?course_id=<?=$c['id']?>" class="btn btn-secondary btn-sm"><i class="fas fa-user-check"></i> Attendance</a>
        <a href="assessments.php?course_id=<?=$c['id']?>" class="btn btn-secondary btn-sm"><i class="fas fa-tasks"></i> Assessments</a>
      </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fas fa-inbox"></i> Ungraded Submissions</div>
    <a href="submissions.php" class="btn btn-warning btn-sm"><i class="fas fa-star"></i> Grade All</a>
  </div>
  <?php $subs=mysqli_query($conn,"SELECT s.id,st.name as student,a.title as assessment,c.course_code,s.submitted_at FROM submissions s JOIN students st ON s.student_id=st.id JOIN assessments a ON s.assessment_id=a.id JOIN courses c ON a.course_id=c.id LEFT JOIN grades g ON s.id=g.submission_id WHERE c.faculty_id=$fid AND g.id IS NULL ORDER BY s.submitted_at DESC LIMIT 8"); ?>
  <?php if(!$subs||mysqli_num_rows($subs)===0): ?>
    <p class="text-muted"><i class="fas fa-check-circle" style="color:var(--success)"></i> All caught up — no ungraded submissions!</p>
  <?php else: ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Student</th><th>Assessment</th><th>Course</th><th>Submitted</th></tr></thead>
    <tbody>
    <?php while($r=mysqli_fetch_assoc($subs)): ?>
    <tr>
      <td><?=htmlspecialchars($r['student'])?></td>
      <td><?=htmlspecialchars($r['assessment'])?></td>
      <td><span class="badge badge-gray"><?=htmlspecialchars($r['course_code'])?></span></td>
      <td class="text-muted" style="font-size:12px;"><?=date('d M, H:i',strtotime($r['submitted_at']))?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
</div><!-- left -->

<div>
<div class="card">
  <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-bullhorn"></i> Announcements</div>
  <?php
  mysqli_query($conn,"CREATE TABLE IF NOT EXISTS announcements (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, body TEXT NOT NULL, role ENUM('all','student','faculty') DEFAULT 'all', posted_by INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
  $ann=mysqli_query($conn,"SELECT * FROM announcements WHERE role IN ('all','faculty') ORDER BY created_at DESC LIMIT 8");
  ?>
  <?php if(!$ann||mysqli_num_rows($ann)===0): ?>
    <p class="text-muted" style="font-size:13px;">No announcements yet.</p>
  <?php else: ?>
  <?php while($a=mysqli_fetch_assoc($ann)): ?>
  <div class="announcement-item">
    <div class="announcement-icon"><i class="fas fa-megaphone"></i></div>
    <div>
      <div class="announcement-title"><?=htmlspecialchars($a['title'])?></div>
      <div class="announcement-body"><?=nl2br(htmlspecialchars($a['body']))?></div>
      <div class="announcement-meta"><i class="far fa-clock"></i> <?=date('d M Y',strtotime($a['created_at']))?></div>
    </div>
  </div>
  <?php endwhile; ?>
  <?php endif; ?>
</div>
</div><!-- right -->
</div><!-- grid -->

<?php include 'footer.php'; ?>
</body></html>
