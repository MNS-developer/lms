<?php
require_once '../config.php';
$sid = $_SESSION['user_id'];

if (isset($_GET['cancel'])) {
    $eid = (int)$_GET['cancel'];
    mysqli_query($conn, "DELETE FROM enrollments WHERE id=$eid AND student_id=$sid AND status='pending'");
    redirect('enrollments.php');
}

$enrollments = mysqli_query($conn, "SELECT e.*, c.course_code, c.course_name, c.credits, f.name as faculty_name
    FROM enrollments e JOIN courses c ON e.course_id=c.id LEFT JOIN faculty f ON c.faculty_id=f.id
    WHERE e.student_id=$sid ORDER BY e.enrolled_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Enrollments — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-list-check"></i> My Enrollments</div>
</div>

<div class="card">
    <h2>All Enrollment Requests</h2>
    <table>
        <tr><th>Course</th><th>Instructor</th><th>Credits</th><th>Status</th><th>Date</th><th>Action</th></tr>
        <?php while ($e = mysqli_fetch_assoc($enrollments)): ?>
        <tr>
            <td><?= htmlspecialchars($e['course_code']) ?> - <?= htmlspecialchars($e['course_name']) ?></td>
            <td><?= $e['faculty_name'] ? htmlspecialchars($e['faculty_name']) : 'TBA' ?></td>
            <td><?= $e['credits'] ?></td>
            <td>
                <?php if ($e['status']==='approved'): ?>
                    <span class="badge badge-success">Approved</span>
                <?php elseif ($e['status']==='pending'): ?>
                    <span class="badge badge-warning">Pending</span>
                <?php else: ?>
                    <span class="badge badge-danger">Rejected</span>
                <?php endif; ?>
            </td>
            <td><?= date('d M Y', strtotime($e['enrolled_at'])) ?></td>
            <td>
                <?php if ($e['status']==='pending'): ?>
                    <a href="?cancel=<?= $e['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Cancel request?')">Cancel</a>
                <?php else: ?>—<?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
