<?php
require_once '../config.php';
$fid = $_SESSION['user_id'];

$courses    = mysqli_query($conn, "SELECT * FROM courses WHERE faculty_id=$fid ORDER BY course_code");
$sel_course = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$where      = $sel_course ? "AND c.id=$sel_course" : '';

$grades = mysqli_query($conn,
    "SELECT st.roll_number, st.name as student, c.course_code, c.course_name,
     a.title as assessment, a.type, a.total_marks, g.marks, g.grade, g.remarks, g.graded_at
     FROM grades g
     JOIN submissions s  ON g.submission_id=s.id
     JOIN assessments a  ON s.assessment_id=a.id
     JOIN courses c      ON a.course_id=c.id
     JOIN students st    ON s.student_id=st.id
     WHERE c.faculty_id=$fid $where
     ORDER BY c.course_code, st.roll_number, a.title");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Grades — Learnix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-header">
  <div class="page-title"><i class="fas fa-star"></i> Grades</div>
</div>

<div class="card" style="padding:14px 20px;">
    <form method="GET" style="display:flex;gap:14px;align-items:flex-end;">
        <div class="form-group" style="margin:0;">
            <label>Filter by Course</label>
            <select name="course_id">
                <option value="">-- All Courses --</option>
                <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                <option value="<?= $c['id'] ?>" <?= $sel_course==$c['id']?'selected':'' ?>>
                    <?= htmlspecialchars($c['course_code']) ?> - <?= htmlspecialchars($c['course_name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($sel_course): ?>
        <a href="grades.php" class="btn btn-warning">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>All Grades</h2>
    <table>
        <tr><th>Roll No</th><th>Student</th><th>Course</th><th>Assessment</th><th>Type</th><th>Marks</th><th>Grade</th><th>Remarks</th><th>Graded On</th></tr>
        <?php $count = 0; while ($g = mysqli_fetch_assoc($grades)): $count++; ?>
        <tr>
            <td><?= htmlspecialchars($g['roll_number']) ?></td>
            <td><?= htmlspecialchars($g['student']) ?></td>
            <td><?= htmlspecialchars($g['course_code']) ?></td>
            <td><?= htmlspecialchars($g['assessment']) ?></td>
            <td><span class="badge badge-info"><?= $g['type'] ?></span></td>
            <td><strong><?= $g['marks'] ?></strong>/<?= $g['total_marks'] ?></td>
            <td><span class="badge badge-success"><?= $g['grade'] ?></span></td>
            <td><?= $g['remarks'] ? htmlspecialchars($g['remarks']) : '—' ?></td>
            <td style="font-size:12px;color:#888;"><?= date('d M Y', strtotime($g['graded_at'])) ?></td>
        </tr>
        <?php endwhile; ?>
        <?php if ($count === 0): ?>
        <tr><td colspan="9" style="text-align:center;color:#aaa;padding:20px;">No grades yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
