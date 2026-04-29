<?php
require_once '../config.php';
$sid = $_SESSION['user_id'];
$cid = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

$course = mysqli_fetch_assoc(mysqli_query($conn,"SELECT c.*,f.name as faculty_name FROM courses c
    LEFT JOIN faculty f ON c.faculty_id=f.id
    JOIN enrollments e ON c.id=e.course_id
    WHERE c.id=$cid AND e.student_id=$sid AND e.status='approved'"));
if (!$course) redirect('dashboard.php');

$total   = (int)mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM attendance WHERE student_id=$sid AND course_id=$cid"))[0];
$present = (int)mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM attendance WHERE student_id=$sid AND course_id=$cid AND status='present'"))[0];
$absent  = $total - $present;
$pct     = $total > 0 ? round($present/$total*100) : 0;
$needed  = 0;
if ($pct < 75 && $total > 0) $needed = max(0,(int)ceil((0.75*$total - $present)/0.25));

$monthly_raw = mysqli_query($conn,"SELECT DATE_FORMAT(date,'%b %Y') as month, SUM(status='present') as p, SUM(status='absent') as a FROM attendance WHERE student_id=$sid AND course_id=$cid GROUP BY YEAR(date),MONTH(date) ORDER BY YEAR(date),MONTH(date)");
$months=[]; $m_p=[]; $m_a=[];
while($row=mysqli_fetch_assoc($monthly_raw)){ $months[]=$row['month']; $m_p[]=(int)$row['p']; $m_a[]=(int)$row['a']; }

$all_records=[];
$cal_data=[];
$res=mysqli_query($conn,"SELECT * FROM attendance WHERE student_id=$sid AND course_id=$cid ORDER BY date ASC");
while($row=mysqli_fetch_assoc($res)){ $all_records[]=$row; $cal_data[$row['date']]=$row['status']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Attendance — <?= htmlspecialchars($course['course_code']) ?></title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

<div class="page-header">
  <div class="page-title"><i class="fas fa-chart-pie"></i> Attendance &mdash; <?= htmlspecialchars($course['course_code']) ?></div>
</div>
<p style="color:var(--text-2);font-size:13px;margin-bottom:20px;"><?= htmlspecialchars($course['course_name']) ?> &nbsp;&middot;&nbsp; <?= $course['faculty_name'] ? htmlspecialchars($course['faculty_name']) : 'N/A' ?></p>

<!-- Summary -->
<div class="card" style="margin-bottom:16px;">
  <div class="att-summary">
    <div class="att-gauge-wrap">
      <canvas id="gaugeChart" width="140" height="140"></canvas>
      <div class="att-gauge-label">
        <div class="big-pct"><?= $pct ?>%</div>
        <div class="small-label">Attendance</div>
      </div>
    </div>
    <div class="att-mini-stats">
      <div class="att-mini-stat"><div class="att-dot" style="background:#16a34a;"></div><span class="ms-label">Present</span><span class="ms-val"><?= $present ?></span></div>
      <div class="att-mini-stat"><div class="att-dot" style="background:#dc2626;"></div><span class="ms-label">Absent</span><span class="ms-val"><?= $absent ?></span></div>
      <div class="att-mini-stat"><div class="att-dot" style="background:#2563eb;"></div><span class="ms-label">Total Classes</span><span class="ms-val"><?= $total ?></span></div>
      <div style="margin-top:8px;">
        <div style="font-size:12px;color:var(--text-2);margin-bottom:5px;">75% Requirement</div>
        <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= min($pct,100) ?>%;background:<?= $pct>=75?'#16a34a':'#dc2626' ?>;"></div></div>
        <div style="font-size:11px;color:var(--text-3);margin-top:3px;"><?= $pct ?>% / 75% required</div>
      </div>
    </div>
  </div>
</div>

<!-- Banner -->
<?php if ($total===0): ?>
<div class="att-banner na"><i class="fas fa-inbox"></i> No attendance records yet for this course.</div>
<?php elseif ($pct<75): ?>
<div class="att-banner warn"><i class="fas fa-exclamation-triangle"></i>
  Your attendance is <strong><?= $pct ?>%</strong> — below the 75% requirement.
  <?php if($needed>0): ?> Attend <strong><?= $needed ?> more</strong> consecutive class<?= $needed>1?'es':'' ?> to recover.<?php endif; ?>
</div>
<?php else: ?>
<div class="att-banner ok"><i class="fas fa-check-circle"></i> Good standing! Your attendance is <strong><?= $pct ?>%</strong>.</div>
<?php endif; ?>

<!-- Tabs -->
<div class="tabs">
  <button class="tab-btn active" onclick="switchTab('charts',this)"><i class="fas fa-chart-bar"></i> Charts</button>
  <button class="tab-btn" onclick="switchTab('table',this)"><i class="fas fa-list"></i> Records</button>
  <button class="tab-btn" onclick="switchTab('calendar',this)"><i class="fas fa-calendar"></i> Calendar</button>
</div>

<div id="tab-charts" class="tab-panel active">
  <div style="display:flex;gap:16px;flex-wrap:wrap;">
    <div class="card" style="flex:0 0 260px;margin-bottom:0;">
      <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-chart-pie"></i> Breakdown</div>
      <canvas id="pieChart" style="max-height:200px;"></canvas>
    </div>
    <div class="card" style="flex:1;min-width:260px;margin-bottom:0;">
      <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-chart-bar"></i> Monthly Trend</div>
      <?php if(count($months)===0): ?><p class="text-muted" style="font-size:13px;">No data yet.</p>
      <?php else: ?><canvas id="barChart" style="max-height:200px;"></canvas><?php endif; ?>
    </div>
  </div>
</div>

<div id="tab-table" class="tab-panel">
  <div class="card">
    <div class="table-wrap"><table>
      <thead><tr><th>#</th><th>Date</th><th>Day</th><th>Status</th></tr></thead>
      <tbody>
      <?php if(count($all_records)===0): ?>
        <tr><td colspan="4" class="text-muted text-center" style="padding:20px;">No records yet.</td></tr>
      <?php else: $i=count($all_records); foreach(array_reverse($all_records) as $a): ?>
      <tr>
        <td class="text-muted" style="font-size:12px;"><?= $i-- ?></td>
        <td><?= date('d M Y',strtotime($a['date'])) ?></td>
        <td class="text-muted"><?= date('l',strtotime($a['date'])) ?></td>
        <td><?= $a['status']==='present'
          ? '<span class="badge badge-success"><i class="fas fa-check"></i> Present</span>'
          : '<span class="badge badge-danger"><i class="fas fa-times"></i> Absent</span>' ?></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table></div>
  </div>
</div>

<div id="tab-calendar" class="tab-panel">
  <div class="card">
  <?php if(count($cal_data)===0): ?>
    <p class="text-muted">No records yet.</p>
  <?php else:
    $dates=$al=array_keys($cal_data);
    $start_ts=strtotime(date('Y-m-01',strtotime(min($dates))));
    $end_ts  =strtotime(date('Y-m-01',strtotime(max($dates))));
    $cur=$start_ts;
    while($cur<=$end_ts):
      $dim=(int)date('t',$cur); $fdow=(int)date('w',$cur);
  ?>
  <div class="cal-month-title"><?= date('F Y',$cur) ?></div>
  <div class="cal-grid" style="margin-bottom:20px;">
    <?php foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d): ?><div class="cal-day-hdr"><?=$d?></div><?php endforeach; ?>
    <?php for($e=0;$e<$fdow;$e++): ?><div class="cal-cell empty"></div><?php endfor; ?>
    <?php for($d=1;$d<=$dim;$d++):
      $ds=date('Y-m',$cur).'-'.str_pad($d,2,'0',STR_PAD_LEFT);
      $st=$cal_data[$ds]??null;
      $cls=$st==='present'?'present':($st==='absent'?'absent':'no-class');
    ?><div class="cal-cell <?=$cls?>"><?=$d?></div><?php endfor; ?>
  </div>
  <?php $cur=strtotime('+1 month',$cur); endwhile; ?>
  <div style="display:flex;gap:16px;font-size:12px;margin-top:4px;flex-wrap:wrap;">
    <span><span style="display:inline-block;width:12px;height:12px;background:var(--success-pale);border-radius:2px;vertical-align:middle;margin-right:4px;"></span>Present</span>
    <span><span style="display:inline-block;width:12px;height:12px;background:var(--danger-pale);border-radius:2px;vertical-align:middle;margin-right:4px;"></span>Absent</span>
    <span><span style="display:inline-block;width:12px;height:12px;background:var(--surface-2);border-radius:2px;vertical-align:middle;margin-right:4px;"></span>No class</span>
  </div>
  <?php endif; ?>
  </div>
</div>

<script>
function switchTab(name,btn){
  document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  btn.classList.add('active');
}
const present=<?=$present?>,absent=<?=$absent?>,total=<?=$total?>;
new Chart(document.getElementById('gaugeChart'),{
  type:'doughnut',
  data:{datasets:[{data:total>0?[present,absent]:[1,0],backgroundColor:total>0?['#16a34a','#dc2626']:['#e2e8f0'],borderWidth:0,borderRadius:4}]},
  options:{cutout:'72%',plugins:{legend:{display:false},tooltip:{enabled:total>0}},animation:{duration:900}}
});
new Chart(document.getElementById('pieChart'),{
  type:'pie',
  data:{labels:['Present','Absent'],datasets:[{data:total>0?[present,absent]:[1,0],backgroundColor:['#16a34a','#dc2626'],borderWidth:2,borderColor:'#fff'}]},
  options:{plugins:{legend:{position:'bottom',labels:{font:{size:12},padding:14}}}}
});
<?php if(count($months)>0): ?>
new Chart(document.getElementById('barChart'),{
  type:'bar',
  data:{labels:<?=json_encode($months)?>,datasets:[
    {label:'Present',data:<?=json_encode($m_p)?>,backgroundColor:'#16a34a',borderRadius:4},
    {label:'Absent', data:<?=json_encode($m_a)?>,backgroundColor:'#dc2626',borderRadius:4}
  ]},
  options:{responsive:true,plugins:{legend:{position:'top',labels:{font:{size:12}}}},
    scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'#f1f5f9'}}}}
});
<?php endif; ?>
</script>
<?php include 'footer.php'; ?>
</body></html>
