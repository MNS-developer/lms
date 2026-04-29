<?php require_once '../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Dashboard — Learnix</title>
</head>
<body>
<?php include 'header.php';
$students = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM students"))[0];
$faculty  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM faculty"))[0];
$courses  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM courses"))[0];
$pending  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM enrollments WHERE status='pending'"))[0];
$approved = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM enrollments WHERE status='approved'"))[0];
?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-chart-line"></i> Dashboard</div>
</div>
<div class="stats">
  <div class="stat-box"><div class="stat-icon"><i class="fas fa-user-graduate"></i></div><div class="num"><?=$students?></div><div class="label">Students</div></div>
  <div class="stat-box green"><div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div><div class="num"><?=$faculty?></div><div class="label">Faculty</div></div>
  <div class="stat-box violet"><div class="stat-icon"><i class="fas fa-book"></i></div><div class="num"><?=$courses?></div><div class="label">Courses</div></div>
  <div class="stat-box orange"><div class="stat-icon"><i class="fas fa-clipboard-check"></i></div><div class="num"><?=$approved?></div><div class="label">Enrollments</div></div>
  <div class="stat-box red"><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div><div class="num"><?=$pending?></div><div class="label">Pending Approvals</div></div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fas fa-hourglass-half"></i> Pending Enrollment Approvals</div>
    <a href="enrollments.php" class="btn btn-secondary btn-sm">View All</a>
  </div>
  <?php $result=mysqli_query($conn,"SELECT e.id,s.name as student,s.roll_number,c.course_name,c.course_code,e.enrolled_at FROM enrollments e JOIN students s ON e.student_id=s.id JOIN courses c ON e.course_id=c.id WHERE e.status='pending' ORDER BY e.enrolled_at DESC LIMIT 12"); ?>
  <?php if(mysqli_num_rows($result)===0): ?>
    <p class="text-muted"><i class="fas fa-check-circle" style="color:var(--success)"></i> No pending enrollments.</p>
  <?php else: ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Student</th><th>Roll No</th><th>Course</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php while($row=mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><strong><?=htmlspecialchars($row['student'])?></strong></td>
      <td><span class="badge badge-gray"><?=htmlspecialchars($row['roll_number'])?></span></td>
      <td><?=htmlspecialchars($row['course_code'])?> &mdash; <?=htmlspecialchars($row['course_name'])?></td>
      <td class="text-muted" style="font-size:12px;"><?=date('d M Y',strtotime($row['enrolled_at']))?></td>
      <td style="white-space:nowrap;">
        <a href="enrollments.php?approve=<?=$row['id']?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</a>
        <a href="enrollments.php?reject=<?=$row['id']?>"  class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Reject</a>
      </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
<div class="card">
  <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-user-graduate"></i> Recent Students</div>
  <?php $rs=mysqli_query($conn,"SELECT name,email,roll_number,created_at FROM students ORDER BY created_at DESC LIMIT 5"); ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Name</th><th>Roll No</th><th>Joined</th></tr></thead>
    <tbody>
    <?php while($r=mysqli_fetch_assoc($rs)): ?>
    <tr>
      <td><strong><?=htmlspecialchars($r['name'])?></strong><br><small class="text-muted"><?=htmlspecialchars($r['email'])?></small></td>
      <td><?=htmlspecialchars($r['roll_number'])?></td>
      <td class="text-muted" style="font-size:12px;"><?=date('d M',strtotime($r['created_at']))?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table></div>
  <div class="mt-2"><a href="students.php" class="btn btn-secondary btn-sm">All Students</a></div>
</div>
<div class="card">
  <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-book"></i> Active Courses</div>
  <?php $rc=mysqli_query($conn,"SELECT c.course_code,c.course_name,f.name as faculty,(SELECT COUNT(*) FROM enrollments e WHERE e.course_id=c.id AND e.status='approved') as enrolled FROM courses c LEFT JOIN faculty f ON c.faculty_id=f.id ORDER BY enrolled DESC LIMIT 5"); ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Code</th><th>Course</th><th>Students</th></tr></thead>
    <tbody>
    <?php while($r=mysqli_fetch_assoc($rc)): ?>
    <tr>
      <td><span class="badge badge-info"><?=htmlspecialchars($r['course_code'])?></span></td>
      <td><?=htmlspecialchars($r['course_name'])?><br><small class="text-muted"><?=$r['faculty']?htmlspecialchars($r['faculty']):'Unassigned'?></small></td>
      <td><span class="badge badge-success"><?=$r['enrolled']?></span></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table></div>
  <div class="mt-2"><a href="courses.php" class="btn btn-secondary btn-sm">All Courses</a></div>
</div>
</div>

<?php include 'footer.php'; ?>
</body></html>
