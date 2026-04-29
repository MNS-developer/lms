<?php
require_once '../config.php';
$sid = $_SESSION['user_id'];
$cid = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$msg = '';

$course = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT c.*, f.name as faculty_name FROM courses c
     LEFT JOIN faculty f ON c.faculty_id=f.id
     JOIN enrollments e ON c.id=e.course_id
     WHERE c.id=$cid AND e.student_id=$sid AND e.status='approved'"));
if (!$course) redirect('dashboard.php');

// ── SUBMIT ──
if (isset($_POST['submit'])) {
    $aid   = (int)$_POST['assessment_id'];
    $check = mysqli_fetch_row(mysqli_query($conn, "SELECT id FROM assessments WHERE id=$aid AND course_id=$cid"));
    if (!$check) {
        $msg = '<div class="alert alert-danger">Invalid assessment.</div>';
    } else {
        $already = mysqli_fetch_row(mysqli_query($conn,
            "SELECT id FROM submissions WHERE assessment_id=$aid AND student_id=$sid"));
        if ($already) {
            $msg = '<div class="alert alert-danger">You already submitted this assessment.</div>';
        } else {
            $filename = null;
            $file_present = isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE;
            if ($file_present) {
                $error    = '';
                $filename = handle_upload('file', UPLOAD_SUBMISSIONS_PATH, $error);
                if (!$filename) {
                    $msg = '<div class="alert alert-danger">Upload error: ' . $error . '</div>';
                }
            }
            if (!$msg) {
                $fn = $filename ? "'" . sanitize($conn, $filename) . "'" : 'NULL';
                mysqli_query($conn, "INSERT INTO submissions (assessment_id,student_id,filename) VALUES ($aid,$sid,$fn)");
                $msg = '<div class="alert alert-success">✅ Submitted successfully!</div>';
            }
        }
    }
}

$assessments = mysqli_query($conn,
    "SELECT a.*,
     s.id as sub_id, s.filename as sub_file, s.submitted_at,
     g.marks, g.grade, g.remarks
     FROM assessments a
     LEFT JOIN submissions s ON a.id=s.assessment_id AND s.student_id=$sid
     LEFT JOIN grades g ON s.id=g.submission_id
     WHERE a.course_id=$cid ORDER BY a.deadline IS NULL, a.deadline ASC, a.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Assessments — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-tasks"></i> Assessments — <?= htmlspecialchars($course['course_code']) ?> <?= htmlspecialchars($course['course_name']) ?></div>
</div>
<a href="dashboard.php" style="font-size:13px;">&larr; Back to Dashboard</a><br><br>
<?= $msg ?>

<?php $count = mysqli_num_rows($assessments); ?>
<?php if ($count === 0): ?>
<div class="card"><p style="color:#888;">No assessments posted yet.</p></div>
<?php endif; ?>

<?php while ($a = mysqli_fetch_assoc($assessments)):
    $overdue   = $a['deadline'] && strtotime($a['deadline']) < time();
    $submitted = (bool)$a['sub_id'];
?>
<div class="card" style="border-left:4px solid <?= $submitted ? '#2ecc71' : ($overdue ? '#e74c3c' : '#3498db') ?>;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
        <div>
            <h2 style="margin-bottom:4px;font-size:16px;">
                <?= htmlspecialchars($a['title']) ?>
                <span class="badge badge-info" style="font-size:11px;vertical-align:middle;"><?= $a['type'] ?></span>
                <?php if ($submitted): ?>
                    <span class="badge badge-success" style="font-size:11px;vertical-align:middle;">✅ Submitted</span>
                <?php elseif ($overdue): ?>
                    <span class="badge badge-danger" style="font-size:11px;vertical-align:middle;">⏰ Overdue</span>
                <?php else: ?>
                    <span class="badge badge-warning" style="font-size:11px;vertical-align:middle;">Pending</span>
                <?php endif; ?>
            </h2>
            <?php if ($a['description']): ?>
            <p style="color:#555;margin:4px 0;font-size:13px;"><?= nl2br(htmlspecialchars($a['description'])) ?></p>
            <?php endif; ?>
        </div>
        <div style="text-align:right;font-size:12px;color:#777;white-space:nowrap;">
            <div>Total Marks: <strong><?= $a['total_marks'] ?></strong></div>
            <?php if ($a['deadline']): ?>
            <div style="color:<?= $overdue ? '#e74c3c' : '#27ae60' ?>;">
                <?= $overdue ? '⏰ Was due' : '📅 Due' ?>: <?= date('d M Y, h:i A', strtotime($a['deadline'])) ?>
            </div>
            <?php else: ?>
            <div style="color:#aaa;">No deadline</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($submitted): ?>
    <div class="alert alert-success" style="margin-top:12px;">
        ✅ Submitted on <?= date('d M Y, h:i A', strtotime($a['submitted_at'])) ?>
        <?php if ($a['sub_file']): ?>
        &nbsp;|&nbsp;
        <a href="<?= LMS_BASE ?>/download.php?type=submission&file=<?= rawurlencode($a['sub_file']) ?>"
           class="btn btn-info btn-sm" style="margin-left:4px;">⬇ View My Submission</a>
        <?php else: ?>
        <span style="color:#888;">&nbsp;(no file attached)</span>
        <?php endif; ?>
        <?php if ($a['grade'] !== null): ?>
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid #c3e6cb;">
            Marks: <strong><?= $a['marks'] ?>/<?= $a['total_marks'] ?></strong>
            &nbsp;|&nbsp; Grade: <strong style="font-size:16px;"><?= $a['grade'] ?></strong>
            <?php if ($a['remarks']): ?>
            <br>Remarks: <em><?= htmlspecialchars($a['remarks']) ?></em>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div style="margin-top:8px;color:#856404;">⏳ Awaiting grading...</div>
        <?php endif; ?>
    </div>

    <?php elseif ($overdue): ?>
    <div class="alert alert-danger" style="margin-top:12px;">⏰ Deadline has passed. Submission is closed.</div>

    <?php else: ?>
    <form method="POST" enctype="multipart/form-data"
          style="margin-top:12px;background:#f9f9f9;padding:14px;border-radius:6px;border:1px solid #eee;">
        <input type="hidden" name="assessment_id" value="<?= $a['id'] ?>">
        <div class="form-group" style="margin-bottom:10px;">
            <label>Upload File <span style="color:#888;font-weight:normal;">(PDF, DOC, DOCX, PPT, ZIP, JPG, PNG)</span></label>
            <input type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip,.jpg,.jpeg,.png">
            <small style="color:#888;display:block;margin-top:4px;">File is optional — you can submit without a file.</small>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">📤 Submit</button>
    </form>
    <?php endif; ?>
</div>
<?php endwhile; ?>

<?php include 'footer.php'; ?>
</body>
</html>
