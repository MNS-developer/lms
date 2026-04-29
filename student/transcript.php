<?php
require_once '../config.php';
$sid = $_SESSION['user_id'];
$s   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM students WHERE id=$sid"));

$grades = mysqli_query($conn,"SELECT c.course_code,c.course_name,c.credits,f.name as faculty_name,
    a.title as assessment_title,a.type,a.total_marks,g.marks,g.grade
    FROM grades g JOIN submissions s ON g.submission_id=s.id
    JOIN assessments a ON s.assessment_id=a.id
    JOIN courses c ON a.course_id=c.id
    LEFT JOIN faculty f ON c.faculty_id=f.id
    WHERE s.student_id=$sid ORDER BY c.course_code,a.title");

$by_course = [];
while ($g = mysqli_fetch_assoc($grades)) {
    $code = $g['course_code'];
    if (!isset($by_course[$code])) {
        $by_course[$code] = [
            'course_name'=>$g['course_name'],
            'credits'=>$g['credits'],
            'faculty'=>$g['faculty_name'],
            'items'=>[]
        ];
    }
    $by_course[$code]['items'][] = $g;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Transcript — Learnix</title>
</head>
<body>
<?php include 'header.php'; ?>

<div class="page-header no-print">
  <div class="page-title"><i class="fas fa-file-alt"></i> Academic Transcript</div>
  <button onclick="window.print()" class="btn btn-primary no-print">
    <i class="fas fa-print"></i> Print
  </button>
</div>

<div class="print-doc">
  <div class="print-doc-header">
    <div>
      <div class="inst-name">University of Engineering &amp; IT</div>
      <div class="inst-sub">Official Academic Transcript</div>
    </div>
    <div style="text-align:right;">
      <div style="font-size:13px;font-weight:700;color:var(--primary);">ACADEMIC TRANSCRIPT</div>
      <div style="font-size:11px;color:var(--text-3);">Learnix LMS &middot; <?= date('Y') ?></div>
    </div>
  </div>

  <div class="print-doc-body">
    <!-- Student info -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 20px;margin-bottom:24px;">
      <div class="info-row"><span class="info-label">Student Name</span><span class="info-val"><?= htmlspecialchars($s['name']) ?></span></div>
      <div class="info-row"><span class="info-label">Roll Number</span><span class="info-val"><?= htmlspecialchars($s['roll_number']) ?></span></div>
      <div class="info-row"><span class="info-label">Email</span><span class="info-val"><?= htmlspecialchars($s['email']) ?></span></div>
      <div class="info-row"><span class="info-label">Generated</span><span class="info-val"><?= date('d M Y H:i') ?></span></div>
    </div>

    <?php if (empty($by_course)): ?>
      <div style="padding:24px;text-align:center;color:var(--text-3);">
        <i class="fas fa-folder-open" style="font-size:28px;margin-bottom:8px;display:block;"></i>
        No graded assessments on record yet.
      </div>
    <?php else: ?>
    <?php foreach ($by_course as $code => $data): ?>
    <div style="margin-bottom:24px;">
      <div style="background:var(--surface-2);border-left:3px solid var(--primary);padding:9px 14px;border-radius:0 var(--radius-sm) var(--radius-sm) 0;margin-bottom:8px;">
        <strong><?= htmlspecialchars($code) ?></strong> &mdash; <?= htmlspecialchars($data['course_name']) ?>
        <span style="color:var(--text-3);font-size:12px;"> &nbsp;|&nbsp; <?= $data['credits'] ?> credits &nbsp;|&nbsp;
          <?= $data['faculty'] ? htmlspecialchars($data['faculty']) : 'N/A' ?></span>
      </div>
      <div class="table-wrap"><table>
        <thead><tr><th>Assessment</th><th>Type</th><th>Marks</th><th>Total</th><th>%</th><th>Grade</th></tr></thead>
        <tbody>
        <?php foreach ($data['items'] as $a):
          $pct = $a['total_marks'] > 0 ? round($a['marks']/$a['total_marks']*100) : 0;
          $gcls = $pct >= 85 ? 'badge-success' : ($pct >= 60 ? 'badge-warning' : 'badge-danger');
        ?>
        <tr>
          <td><?= htmlspecialchars($a['assessment_title']) ?></td>
          <td><span class="badge badge-gray"><?= $a['type'] ?></span></td>
          <td><strong><?= $a['marks'] ?></strong></td>
          <td class="text-muted"><?= $a['total_marks'] ?></td>
          <td><?= $pct ?>%</td>
          <td><span class="badge <?= $gcls ?>"><?= htmlspecialchars($a['grade']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-top:32px;padding-top:16px;border-top:1px solid var(--border);">
      <div style="font-size:11px;color:var(--text-3);">This document is system-generated and is subject to verification.</div>
      <div class="print-timestamp">Printed: <?= date('d M Y, H:i') ?></div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
