<?php
require_once '../config.php';
$sid = $_SESSION['user_id'];
$s   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM students WHERE id=$sid"));
$enr = mysqli_query($conn,"SELECT c.course_code,c.course_name,c.credits,f.name as faculty_name
    FROM enrollments e JOIN courses c ON e.course_id=c.id LEFT JOIN faculty f ON c.faculty_id=f.id
    WHERE e.student_id=$sid AND e.status='approved' ORDER BY c.course_code");
$total_credits = 0;
$rows = [];
while ($c = mysqli_fetch_assoc($enr)) { $rows[] = $c; $total_credits += $c['credits']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Roll Slip — Learnix</title>
</head>
<body>
<?php include 'header.php'; ?>

<div class="page-header no-print">
  <div class="page-title"><i class="fas fa-id-card"></i> Roll Number Slip</div>
  <button onclick="window.print()" class="btn btn-primary no-print">
    <i class="fas fa-print"></i> Print
  </button>
</div>

<div class="print-doc">
  <!-- Header -->
  <div class="print-doc-header">
    <div>
      <div class="inst-name">University of Engineering &amp; IT</div>
      <div class="inst-sub">Pakistan</div>
    </div>
    <div style="text-align:right;">
      <div style="font-size:13px;font-weight:700;color:var(--primary);">STUDENT ROLL SLIP</div>
      <div style="font-size:11px;color:var(--text-3);">Academic Session <?= date('Y') ?></div>
    </div>
  </div>

  <div class="print-doc-body">
    <!-- Student Info -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 20px;margin-bottom:22px;">
      <div class="info-row"><span class="info-label">Student Name</span><span class="info-val"><?= htmlspecialchars($s['name']) ?></span></div>
      <div class="info-row"><span class="info-label">Roll Number</span><span class="info-val"><?= htmlspecialchars($s['roll_number']) ?></span></div>
      <div class="info-row"><span class="info-label">Email</span><span class="info-val"><?= htmlspecialchars($s['email']) ?></span></div>
      <div class="info-row"><span class="info-label">Phone</span><span class="info-val"><?= htmlspecialchars($s['phone'] ?: 'N/A') ?></span></div>
      <div class="info-row" style="grid-column:span 2;"><span class="info-label">Address</span><span class="info-val"><?= htmlspecialchars($s['address'] ?: 'N/A') ?></span></div>
    </div>

    <!-- Courses -->
    <div style="font-size:13px;font-weight:700;color:var(--text-1);margin-bottom:10px;padding-bottom:6px;border-bottom:2px solid var(--primary);">
      Enrolled Courses — Current Semester
    </div>
    <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Course Code</th><th>Course Name</th><th>Instructor</th><th>Credits</th></tr></thead>
      <tbody>
      <?php foreach($rows as $i=>$c): ?>
      <tr>
        <td class="text-muted"><?= $i+1 ?></td>
        <td><strong><?= htmlspecialchars($c['course_code']) ?></strong></td>
        <td><?= htmlspecialchars($c['course_name']) ?></td>
        <td class="text-muted"><?= $c['faculty_name'] ? htmlspecialchars($c['faculty_name']) : 'TBA' ?></td>
        <td><?= $c['credits'] ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($rows)): ?>
      <tr><td colspan="5" class="text-muted text-center" style="padding:18px;">No enrolled courses yet.</td></tr>
      <?php endif; ?>
      </tbody>
      <tfoot>
        <tr style="background:var(--surface-2);">
          <td colspan="4" style="text-align:right;font-weight:700;padding:10px 14px;">Total Credit Hours</td>
          <td style="font-weight:700;"><?= $total_credits ?></td>
        </tr>
      </tfoot>
    </table>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-top:40px;padding-top:16px;border-top:1px solid var(--border);">
      <div style="font-size:11px;color:var(--text-3);">
        This is a computer-generated document. No signature required.
      </div>
      <div class="print-timestamp">Generated: <?= date('d M Y, H:i') ?></div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
