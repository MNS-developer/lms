<?php
require_once '../config.php';

if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    mysqli_query($conn, "UPDATE enrollments SET status='approved' WHERE id=$id");
    redirect('enrollments.php');
}
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    mysqli_query($conn, "UPDATE enrollments SET status='rejected' WHERE id=$id");
    redirect('enrollments.php');
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM enrollments WHERE id=$id");
    redirect('enrollments.php');
}

$filter = isset($_GET['status']) ? sanitize($conn, $_GET['status']) : 'all';
$where = $filter !== 'all' ? "WHERE e.status='$filter'" : '';
$enrollments = mysqli_query($conn, "SELECT e.*, s.name as student, s.roll_number, c.course_name, c.course_code
    FROM enrollments e JOIN students s ON e.student_id=s.id JOIN courses c ON e.course_id=c.id
    $where ORDER BY e.enrolled_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Enrollments — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title">Enrollment Management</div>
</div>

<div style="margin-bottom:16px;">
    <a href="?status=all" class="btn <?= $filter==='all'?'btn-primary':'btn-info' ?> btn-sm">All</a>
    <a href="?status=pending" class="btn <?= $filter==='pending'?'btn-primary':'btn-warning' ?> btn-sm">Pending</a>
    <a href="?status=approved" class="btn <?= $filter==='approved'?'btn-primary':'btn-success' ?> btn-sm">Approved</a>
    <a href="?status=rejected" class="btn <?= $filter==='rejected'?'btn-primary':'btn-danger' ?> btn-sm">Rejected</a>
</div>

<div class="card">
    <h2>Enrollments</h2>
    <table>
        <tr><th>Roll No</th><th>Student</th><th>Course</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        <?php while ($e = mysqli_fetch_assoc($enrollments)): ?>
        <tr>
            <td><?= htmlspecialchars($e['roll_number']) ?></td>
            <td><?= htmlspecialchars($e['student']) ?></td>
            <td><?= htmlspecialchars($e['course_code']) ?> - <?= htmlspecialchars($e['course_name']) ?></td>
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
                    <a href="?approve=<?= $e['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                    <a href="?reject=<?= $e['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
                <?php endif; ?>
                <a href="?delete=<?= $e['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Del</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
