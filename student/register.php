<?php
require_once '../config.php';
$sid = $_SESSION['user_id'];
$msg = '';

if (isset($_POST['enroll'])) {
    $cid = (int)$_POST['course_id'];
    // Check not already enrolled
    $exists = mysqli_fetch_row(mysqli_query($conn, "SELECT id FROM enrollments WHERE student_id=$sid AND course_id=$cid"));
    if ($exists) {
        $msg = '<div class="alert alert-danger">You are already enrolled or have a pending request for this course.</div>';
    } else {
        mysqli_query($conn, "INSERT INTO enrollments (student_id,course_id,status) VALUES ($sid,$cid,'pending')");
        $msg = '<div class="alert alert-success">Enrollment request submitted. Waiting for admin approval.</div>';
    }
}

// Available courses (not yet enrolled)
$courses = mysqli_query($conn, "SELECT c.*, f.name as faculty_name FROM courses c
    LEFT JOIN faculty f ON c.faculty_id=f.id
    WHERE c.id NOT IN (SELECT course_id FROM enrollments WHERE student_id=$sid)
    ORDER BY c.course_code");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Course Registration — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-plus-circle"></i> Course Registration</div>
</div>
<?= $msg ?>

<div class="card">
    <h2>Available Courses</h2>
    <?php if (mysqli_num_rows($courses) === 0): ?>
        <p style="color:#888;">No new courses available.</p>
    <?php else: ?>
    <table>
        <tr><th>Code</th><th>Course Name</th><th>Instructor</th><th>Credits</th><th>Action</th></tr>
        <?php while ($c = mysqli_fetch_assoc($courses)): ?>
        <tr>
            <td><?= htmlspecialchars($c['course_code']) ?></td>
            <td><?= htmlspecialchars($c['course_name']) ?></td>
            <td><?= $c['faculty_name'] ? htmlspecialchars($c['faculty_name']) : 'TBA' ?></td>
            <td><?= $c['credits'] ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                    <button type="submit" name="enroll" class="btn btn-primary btn-sm" onclick="return confirm('Enroll in this course?')">Enroll</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
