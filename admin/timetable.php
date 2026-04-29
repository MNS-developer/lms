<?php
require_once '../config.php';
$msg = '';

if (isset($_POST['add'])) {
    $cid   = (int)$_POST['course_id'];
    $day   = sanitize($conn, $_POST['day']);
    $start = sanitize($conn, $_POST['start_time']);
    $end   = sanitize($conn, $_POST['end_time']);
    $room  = sanitize($conn, $_POST['room']);
    $r = mysqli_query($conn, "INSERT INTO timetable (course_id,day,start_time,end_time,room) VALUES ($cid,'$day','$start','$end','$room')");
    if ($r) redirect('timetable.php?added=1');
    else $msg = '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
}
if (isset($_POST['edit'])) {
    $id    = (int)$_POST['id'];
    $cid   = (int)$_POST['course_id'];
    $day   = sanitize($conn, $_POST['day']);
    $start = sanitize($conn, $_POST['start_time']);
    $end   = sanitize($conn, $_POST['end_time']);
    $room  = sanitize($conn, $_POST['room']);
    mysqli_query($conn, "UPDATE timetable SET course_id=$cid,day='$day',start_time='$start',end_time='$end',room='$room' WHERE id=$id");
    redirect('timetable.php?updated=1');
}
if (isset($_GET['delete'])) {
    mysqli_query($conn, "DELETE FROM timetable WHERE id=".(int)$_GET['delete']);
    redirect('timetable.php');
}

$edit = null;
if (isset($_GET['edit']) && !isset($_POST['edit']) && !isset($_POST['add'])) {
    $id   = (int)$_GET['edit'];
    $edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM timetable WHERE id=$id"));
}

if (isset($_GET['added']))   $msg = '<div class="alert alert-success">Slot added.</div>';
if (isset($_GET['updated'])) $msg = '<div class="alert alert-success">Slot updated.</div>';

$courses   = mysqli_query($conn, "SELECT * FROM courses ORDER BY course_code");
$timetable = mysqli_query($conn, "SELECT t.*, c.course_code, c.course_name FROM timetable t
    JOIN courses c ON t.course_id=c.id
    ORDER BY FIELD(t.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), t.start_time");
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
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
  <div class="page-title"><i class="fas fa-calendar-alt"></i> Timetable</div>
</div>
<?= $msg ?>

<div class="card">
    <h2><?= $edit ? '<i class="fas fa-edit"></i> Edit Slot' : '<i class="fas fa-plus"></i> Add Timetable Slot' ?></h2>
    <form method="POST" action="timetable.php<?= $edit ? '?edit='.$edit['id'] : '' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Course</label>
                <select name="course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php mysqli_data_seek($courses, 0); while ($c = mysqli_fetch_assoc($courses)): ?>
                    <option value="<?= $c['id'] ?>" <?= ($edit && $edit['course_id']==$c['id']) ? 'selected':'' ?>>
                        <?= htmlspecialchars($c['course_code']) ?> – <?= htmlspecialchars($c['course_name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Day</label>
                <select name="day" required>
                    <?php foreach($days as $d): ?>
                    <option <?= ($edit && $edit['day']===$d) ? 'selected':'' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="start_time" value="<?= $edit['start_time'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label>End Time</label>
                <input type="time" name="end_time" value="<?= $edit['end_time'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label>Room</label>
                <input name="room" placeholder="e.g. Room 101" value="<?= htmlspecialchars($edit['room'] ?? '') ?>">
            </div>
        </div>
        <button type="submit" name="<?= $edit ? 'edit' : 'add' ?>" class="btn btn-primary">
            <?= $edit ? '💾 Update Slot' : '<i class="fas fa-plus"></i> Add Slot' ?>
        </button>
        <?php if ($edit): ?>
        <a href="timetable.php" class="btn btn-warning" style="margin-left:8px;">✕ Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>Current Timetable</h2>
    <table>
        <tr><th>Course</th><th>Day</th><th>Start</th><th>End</th><th>Room</th><th>Actions</th></tr>
        <?php $count = 0; while ($t = mysqli_fetch_assoc($timetable)): $count++; ?>
        <tr>
            <td><?= htmlspecialchars($t['course_code']) ?> – <?= htmlspecialchars($t['course_name']) ?></td>
            <td><?= $t['day'] ?></td>
            <td><?= date('h:i A', strtotime($t['start_time'])) ?></td>
            <td><?= date('h:i A', strtotime($t['end_time'])) ?></td>
            <td><?= htmlspecialchars($t['room'] ?: '—') ?></td>
            <td>
                <a href="?edit=<?= $t['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                <a href="?delete=<?= $t['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete this slot?')"><i class="fas fa-trash"></i> Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php if ($count === 0): ?>
        <tr><td colspan="6" style="text-align:center;color:#aaa;padding:20px;">No timetable slots yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
