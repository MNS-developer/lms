<?php
require_once '../config.php';
$fid = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Courses — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-book"></i> My Courses</div>
</div>

<?php if (isset($_GET['id'])): ?>
<?php
$cid = (int)$_GET['id'];
$course = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM courses WHERE id=$cid AND faculty_id=$fid"));
if (!$course) { echo '<div class="alert alert-danger">Course not found.</div>'; include 'footer.php'; exit; }
$students = mysqli_query($conn, "SELECT s.*, e.enrolled_at FROM students s JOIN enrollments e ON s.id=e.student_id WHERE e.course_id=$cid AND e.status='approved' ORDER BY s.roll_number");
?>
<div class="card">
    <h2><?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?> &mdash; Enrolled Students</h2>
    <a href="courses.php" style="font-size:13px;">&larr; Back</a>
    <br><br>
    <table>
        <tr><th>Roll No</th><th>Name</th><th>Email</th><th>Enrolled On</th></tr>
        <?php while ($s = mysqli_fetch_assoc($students)): ?>
        <tr>
            <td><?= htmlspecialchars($s['roll_number']) ?></td>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars($s['email']) ?></td>
            <td><?= date('d M Y', strtotime($s['enrolled_at'])) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php else: ?>
<div class="card">
    <h2>All My Courses</h2>
    <table>
        <tr><th>Code</th><th>Name</th><th>Credits</th><th>Students</th><th>Action</th></tr>
        <?php
        $courses = mysqli_query($conn, "SELECT c.*, (SELECT COUNT(*) FROM enrollments e WHERE e.course_id=c.id AND e.status='approved') as enrolled FROM courses c WHERE faculty_id=$fid ORDER BY course_code");
        while ($c = mysqli_fetch_assoc($courses)):
        ?>
        <tr>
            <td><?= htmlspecialchars($c['course_code']) ?></td>
            <td><?= htmlspecialchars($c['course_name']) ?></td>
            <td><?= $c['credits'] ?></td>
            <td><?= $c['enrolled'] ?></td>
            <td><a href="?id=<?= $c['id'] ?>" class="btn btn-info btn-sm">View Students</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
</body>
</html>
