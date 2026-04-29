<?php
require_once '../config.php';
$sid = $_SESSION['user_id'];
$cid = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

$course = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT c.*, f.name as faculty_name FROM courses c
     LEFT JOIN faculty f ON c.faculty_id=f.id
     JOIN enrollments e ON c.id=e.course_id
     WHERE c.id=$cid AND e.student_id=$sid AND e.status='approved'"));
if (!$course) redirect('dashboard.php');

$materials = mysqli_query($conn, "SELECT * FROM materials WHERE course_id=$cid ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Study Materials — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title">📂 Study Materials — <?= htmlspecialchars($course['course_code']) ?> <?= htmlspecialchars($course['course_name']) ?></div>
</div>
<p style="color:#777;margin-bottom:4px;">Instructor: <?= $course['faculty_name'] ? htmlspecialchars($course['faculty_name']) : 'TBA' ?></p>
<a href="dashboard.php" style="font-size:13px;">&larr; Back to Dashboard</a><br><br>

<div class="card">
    <h2>Available Materials</h2>
    <?php if (mysqli_num_rows($materials) === 0): ?>
        <p style="color:#888;">No materials uploaded yet. Check back later.</p>
    <?php else: ?>
    <table>
        <tr><th>#</th><th>Title</th><th>Uploaded On</th><th>Action</th></tr>
        <?php $i = 1; while ($m = mysqli_fetch_assoc($materials)): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($m['title']) ?></td>
            <td><?= date('d M Y', strtotime($m['uploaded_at'])) ?></td>
            <td>
                <a href="<?= LMS_BASE ?>/download.php?type=material&file=<?= rawurlencode($m['filename']) ?>"
                   class="btn btn-success btn-sm">⬇ Download</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
