<?php
require_once '../config.php';
$fid = $_SESSION['user_id'];
$msg = '';

$aid = isset($_GET['assessment_id']) ? (int)$_GET['assessment_id'] : 0;
if (!$aid) redirect('assessments.php');

$assessment = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.*, c.course_name, c.course_code FROM assessments a
     JOIN courses c ON a.course_id=c.id WHERE a.id=$aid AND c.faculty_id=$fid"));
if (!$assessment) redirect('assessments.php');

// ── SAVE GRADE ──
if (isset($_POST['grade'])) {
    $sub_id  = (int)$_POST['submission_id'];
    $marks   = (int)$_POST['marks'];
    $remarks = sanitize($conn, $_POST['remarks']);
    $total   = $assessment['total_marks'];
    $pct     = $total > 0 ? ($marks / $total) * 100 : 0;
    if      ($pct >= 90) $grade = 'A+';
    elseif  ($pct >= 85) $grade = 'A';
    elseif  ($pct >= 80) $grade = 'B+';
    elseif  ($pct >= 75) $grade = 'B';
    elseif  ($pct >= 70) $grade = 'C+';
    elseif  ($pct >= 65) $grade = 'C';
    elseif  ($pct >= 60) $grade = 'D';
    else                 $grade = 'F';

    $exists = mysqli_fetch_row(mysqli_query($conn, "SELECT id FROM grades WHERE submission_id=$sub_id"));
    if ($exists) {
        mysqli_query($conn, "UPDATE grades SET marks=$marks,grade='$grade',remarks='$remarks',graded_at=NOW() WHERE submission_id=$sub_id");
    } else {
        mysqli_query($conn, "INSERT INTO grades (submission_id,marks,grade,remarks) VALUES ($sub_id,$marks,'$grade','$remarks')");
    }
    $msg = '<div class="alert alert-success">✅ Grade saved successfully.</div>';
}

$submissions = mysqli_query($conn,
    "SELECT s.*, st.name as student_name, st.roll_number,
     g.id as grade_id, g.marks, g.grade, g.remarks
     FROM submissions s
     JOIN students st ON s.student_id=st.id
     LEFT JOIN grades g ON s.id=g.submission_id
     WHERE s.assessment_id=$aid ORDER BY st.roll_number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Submissions — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title">Submissions: <?= htmlspecialchars($assessment['title']) ?></div>
</div>
<p style="color:#555;margin-bottom:6px;">
    <?= htmlspecialchars($assessment['course_code']) ?> — <?= htmlspecialchars($assessment['course_name']) ?>
    &nbsp;|&nbsp; Total Marks: <strong><?= $assessment['total_marks'] ?></strong>
    <?php if ($assessment['deadline']): ?>
    &nbsp;|&nbsp; Deadline: <strong><?= date('d M Y H:i', strtotime($assessment['deadline'])) ?></strong>
    <?php endif; ?>
</p>
<a href="assessments.php" style="font-size:13px;">&larr; Back to Assessments</a><br><br>
<?= $msg ?>

<div class="card">
    <h2>Student Submissions</h2>
    <?php $total_rows = mysqli_num_rows($submissions); ?>
    <?php if ($total_rows === 0): ?>
        <p style="color:#888;">No submissions yet.</p>
    <?php else: ?>
    <table>
        <tr><th>Roll No</th><th>Student</th><th>Submitted File</th><th>Date &amp; Time</th><th>Marks</th><th>Grade</th><th>Action</th></tr>
        <?php while ($s = mysqli_fetch_assoc($submissions)): ?>
        <tr>
            <td><?= htmlspecialchars($s['roll_number']) ?></td>
            <td><?= htmlspecialchars($s['student_name']) ?></td>
            <td>
                <?php if ($s['filename']): ?>
                    <a href="<?= LMS_BASE ?>/download.php?type=submission&file=<?= rawurlencode($s['filename']) ?>"
                       class="btn btn-info btn-sm" target="_blank">⬇ Download</a>
                <?php else: ?>
                    <span style="color:#aaa;">No file</span>
                <?php endif; ?>
            </td>
            <td style="font-size:12px;"><?= $s['submitted_at'] ? date('d M Y, h:i A', strtotime($s['submitted_at'])) : '—' ?></td>
            <td><?= $s['marks'] !== null ? '<strong>'.$s['marks'].'</strong>/'.$assessment['total_marks'] : '—' ?></td>
            <td><?= $s['grade'] ? '<span class="badge badge-success">'.$s['grade'].'</span>' : '<span style="color:#aaa;">—</span>' ?></td>
            <td>
                <button onclick="toggleGrade(<?= $s['id'] ?>)" class="btn btn-warning btn-sm">
                    <?= $s['grade_id'] ? '<i class="fas fa-edit"></i> Edit Grade' : '⭐ Grade' ?>
                </button>
            </td>
        </tr>
        <tr id="grade-<?= $s['id'] ?>" style="display:none;background:#fffbf0;">
            <td colspan="7" style="padding:14px 16px;">
                <form method="POST" style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;">
                    <input type="hidden" name="submission_id" value="<?= $s['id'] ?>">
                    <div class="form-group" style="margin:0;">
                        <label>Marks <small style="color:#888;">(max <?= $assessment['total_marks'] ?>)</small></label>
                        <input type="number" name="marks" value="<?= $s['marks'] ?? '' ?>"
                               min="0" max="<?= $assessment['total_marks'] ?>" required style="width:90px;">
                    </div>
                    <div class="form-group" style="margin:0;flex:1;min-width:180px;">
                        <label>Remarks <small style="color:#888;">(optional)</small></label>
                        <input name="remarks" value="<?= htmlspecialchars($s['remarks'] ?? '') ?>" style="width:100%;">
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" name="grade" class="btn btn-success">💾 Save</button>
                        <button type="button" onclick="toggleGrade(<?= $s['id'] ?>)" class="btn btn-warning">Cancel</button>
                    </div>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php endif; ?>
</div>

<script>
function toggleGrade(id) {
    var row = document.getElementById('grade-' + id);
    row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
}
</script>

<?php include 'footer.php'; ?>
</body>
</html>
