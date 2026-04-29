<?php
require_once '../config.php';
$fid = $_SESSION['user_id'];
$msg = '';

if (isset($_POST['save'])) {
    $cid  = (int)$_POST['course_id'];
    $date = sanitize($conn, $_POST['date']);
    $check = mysqli_fetch_row(mysqli_query($conn, "SELECT id FROM courses WHERE id=$cid AND faculty_id=$fid"));
    if ($check && !empty($_POST['attendance'])) {
        foreach ($_POST['attendance'] as $student_id => $status) {
            $s      = (int)$student_id;
            $status = in_array($status, ['present','absent']) ? $status : 'present';
            $exists = mysqli_fetch_row(mysqli_query($conn,
                "SELECT id FROM attendance WHERE student_id=$s AND course_id=$cid AND date='$date'"));
            if ($exists) {
                mysqli_query($conn, "UPDATE attendance SET status='$status' WHERE student_id=$s AND course_id=$cid AND date='$date'");
            } else {
                mysqli_query($conn, "INSERT INTO attendance (student_id,course_id,date,status) VALUES ($s,$cid,'$date','$status')");
            }
        }
        $msg = '<div class="alert alert-success">Attendance saved for ' . date('d M Y', strtotime($date)) . '.</div>';
    } elseif ($check && empty($_POST['attendance'])) {
        $msg = '<div class="alert alert-danger">No students found or no attendance data submitted.</div>';
    }
}

$courses    = mysqli_query($conn, "SELECT * FROM courses WHERE faculty_id=$fid ORDER BY course_code");
$sel_course = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$sel_date   = isset($_GET['date']) ? sanitize($conn, $_GET['date']) : date('Y-m-d');

// After save: also re-set sel_course/sel_date from POST so summary shows correctly
if (isset($_POST['save'])) {
    $sel_course = (int)$_POST['course_id'];
    $sel_date   = sanitize($conn, $_POST['date']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Attendance — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-user-check"></i> Attendance</div>
</div>
<?= $msg ?>

<div class="card">
    <h2>Select Course &amp; Date</h2>
    <form method="GET" style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;">
        <div class="form-group" style="margin:0;">
            <label>Course</label>
            <select name="course_id" required>
                <option value="">-- Select --</option>
                <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                <option value="<?= $c['id'] ?>" <?= $sel_course==$c['id']?'selected':'' ?>>
                    <?= htmlspecialchars($c['course_code']) ?> - <?= htmlspecialchars($c['course_name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label>Date</label>
            <input type="date" name="date" value="<?= $sel_date ?>" max="<?= date('Y-m-d') ?>">
        </div>
        <button type="submit" class="btn btn-primary">Load Students</button>
    </form>
</div>

<?php if ($sel_course): ?>
<?php
$course_info = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM courses WHERE id=$sel_course AND faculty_id=$fid"));
if (!$course_info) { echo '<div class="alert alert-danger">Invalid course.</div>'; include 'footer.php'; exit; }

// Fetch all dates attendance has been marked for this course
$marked_dates_res = mysqli_query($conn,
    "SELECT DISTINCT date FROM attendance WHERE course_id=$sel_course ORDER BY date DESC");
$marked_dates = [];
while ($md = mysqli_fetch_assoc($marked_dates_res)) $marked_dates[] = $md['date'];

// Check if current selected date is already marked
$date_already_marked = in_array($sel_date, $marked_dates);

// Fetch summary: per-date present/absent counts
$summary_res = mysqli_query($conn,
    "SELECT date,
            SUM(status='present') AS present_count,
            SUM(status='absent')  AS absent_count,
            COUNT(*) AS total
     FROM attendance WHERE course_id=$sel_course
     GROUP BY date ORDER BY date DESC");
$summary_rows = [];
while ($sr = mysqli_fetch_assoc($summary_res)) $summary_rows[] = $sr;
?>
<div class="card">
    <h2><i class="fas fa-calendar-check" style="color:var(--primary);"></i> Attendance Summary — <?= htmlspecialchars($course_info['course_code']) ?></h2>
    <?php if (empty($summary_rows)): ?>
        <p style="color:#888;"><i class="fas fa-info-circle"></i> No attendance has been marked yet for this course.</p>
    <?php else: ?>
        <p style="margin-bottom:12px;color:var(--text-2);font-size:13px;">
            <strong><?= count($summary_rows) ?></strong> date(s) marked so far:
        </p>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Day</th>
                    <th style="text-align:center;">Present</th>
                    <th style="text-align:center;">Absent</th>
                    <th style="text-align:center;">Total</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($summary_rows as $i => $sr): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><strong><?= date('d M Y', strtotime($sr['date'])) ?></strong></td>
                <td><?= date('l', strtotime($sr['date'])) ?></td>
                <td style="text-align:center;">
                    <span style="color:#22c55e;font-weight:600;"><?= $sr['present_count'] ?></span>
                </td>
                <td style="text-align:center;">
                    <span style="color:#ef4444;font-weight:600;"><?= $sr['absent_count'] ?></span>
                </td>
                <td style="text-align:center;"><?= $sr['total'] ?></td>
                <td style="text-align:center;">
                    <?php if ($sr['date'] === $sel_date): ?>
                        <span style="background:var(--primary);color:#fff;font-size:11px;font-weight:600;padding:2px 9px;border-radius:20px;">Selected</span>
                    <?php else: ?>
                        <span style="background:#22c55e22;color:#22c55e;font-size:11px;font-weight:600;padding:2px 9px;border-radius:20px;">Marked</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <a href="attendance.php?course_id=<?= $sel_course ?>&date=<?= $sr['date'] ?>"
                       class="btn btn-primary btn-sm"
                       style="font-size:12px;padding:4px 12px;">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$students = mysqli_query($conn,
    "SELECT s.*, a.status as att_status FROM students s
     JOIN enrollments e ON s.id=e.student_id
     LEFT JOIN attendance a ON s.id=a.student_id AND a.course_id=$sel_course AND a.date='$sel_date'
     WHERE e.course_id=$sel_course AND e.status='approved' ORDER BY s.roll_number");
$total_students = mysqli_num_rows($students);
?>
<div class="card">
    <h2>
        Mark Attendance — <?= htmlspecialchars($course_info['course_code']) ?> — <?= date('D, d M Y', strtotime($sel_date)) ?>
        <?php if ($date_already_marked): ?>
            <span style="margin-left:10px;background:#22c55e;color:#fff;font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;vertical-align:middle;">
                <i class="fas fa-check"></i> Already Marked
            </span>
        <?php else: ?>
            <span style="margin-left:10px;background:#f59e0b;color:#fff;font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;vertical-align:middle;">
                <i class="fas fa-clock"></i> Not Marked Yet
            </span>
        <?php endif; ?>
    </h2>
    <?php if ($total_students === 0): ?>
        <p style="color:#888;">No approved students in this course yet.</p>
    <?php else: ?>
    <form method="POST">
        <input type="hidden" name="course_id" value="<?= $sel_course ?>">
        <input type="hidden" name="date" value="<?= $sel_date ?>">
        <div style="margin-bottom:10px;display:flex;gap:10px;">
            <button type="button" onclick="markAll('present')" class="btn btn-success btn-sm">✅ Mark All Present</button>
            <button type="button" onclick="markAll('absent')"  class="btn btn-danger btn-sm">❌ Mark All Absent</button>
        </div>
        <table>
            <tr><th>Roll No</th><th>Student Name</th><th style="width:120px;">Present</th><th style="width:120px;">Absent</th></tr>
            <?php while ($s = mysqli_fetch_assoc($students)):
                $att = $s['att_status'] ?? 'present'; ?>
            <tr class="att-row">
                <td><?= htmlspecialchars($s['roll_number']) ?></td>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td style="text-align:center;">
                    <input type="radio" name="attendance[<?= $s['id'] ?>]" value="present"
                           <?= $att==='present'?'checked':'' ?> class="att-radio">
                </td>
                <td style="text-align:center;">
                    <input type="radio" name="attendance[<?= $s['id'] ?>]" value="absent"
                           <?= $att==='absent'?'checked':'' ?> class="att-radio">
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <button type="submit" name="save" class="btn btn-success">💾 Save Attendance</button>
    </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
function markAll(status) {
    document.querySelectorAll('input[type=radio][value=' + status + ']').forEach(function(r){ r.checked = true; });
}
</script>

<?php include 'footer.php'; ?>
</body>
</html>
