<?php
require_once '../config.php';
$sid = $_SESSION['user_id'];

$timetable = mysqli_query($conn, "SELECT t.*, c.course_code, c.course_name, f.name as faculty_name
    FROM timetable t
    JOIN courses c ON t.course_id=c.id
    LEFT JOIN faculty f ON c.faculty_id=f.id
    JOIN enrollments e ON c.id=e.course_id
    WHERE e.student_id=$sid AND e.status='approved'
    ORDER BY FIELD(t.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), t.start_time");

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$by_day = [];
while ($t = mysqli_fetch_assoc($timetable)) {
    $by_day[$t['day']][] = $t;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Timetable — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title">My Timetable <button onclick="window.print()" class="btn btn-primary btn-sm no-print" style="float:right;"><i class="fas fa-print"></i> Print</button></div>
</div>

<?php if (empty($by_day)): ?>
<div class="card"><p style="color:#888;">No timetable assigned yet.</p></div>
<?php else: ?>
<?php foreach ($days as $day): ?>
    <?php if (!empty($by_day[$day])): ?>
    <div class="card" style="margin-bottom:12px;">
        <h2><?= $day ?></h2>
        <table>
            <tr><th>Time</th><th>Course</th><th>Instructor</th><th>Room</th></tr>
            <?php foreach ($by_day[$day] as $t): ?>
            <tr>
                <td><?= date('h:i A', strtotime($t['start_time'])) ?> &mdash; <?= date('h:i A', strtotime($t['end_time'])) ?></td>
                <td><?= htmlspecialchars($t['course_code']) ?> &mdash; <?= htmlspecialchars($t['course_name']) ?></td>
                <td><?= $t['faculty_name'] ? htmlspecialchars($t['faculty_name']) : 'TBA' ?></td>
                <td><?= htmlspecialchars($t['room'] ?: 'TBA') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>
</body>
</html>
