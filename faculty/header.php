<?php requireLogin('faculty', '../index.php'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">

<nav class="navbar">
  <div style="display:flex;align-items:center;gap:10px;">
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fas fa-bars"></i></button>
    <div class="brand">
      <div class="brand-logo"><i class="fas fa-graduation-cap"></i></div>
      Learnix <span style="color:var(--text-3);font-weight:400;margin:0 6px;">/</span>
      <span style="color:var(--text-3);font-weight:400;font-size:13px;">Faculty</span>
    </div>
  </div>
  <div class="nav-links">
    <span class="welcome-text">Hello, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
    <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</nav>
<div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>
<div class="layout">
<aside class="sidebar" id="sidebar">
  <div class="menu-title">Main</div>
  <a href="dashboard.php"    <?= basename($_SERVER['PHP_SELF'])==='dashboard.php'    ?'class="active"':'' ?>><i class="fas fa-home"></i> Dashboard</a>
  <div class="menu-title">Teaching</div>
  <a href="courses.php"      <?= basename($_SERVER['PHP_SELF'])==='courses.php'      ?'class="active"':'' ?>><i class="fas fa-book"></i> My Courses</a>
  <a href="materials.php"    <?= basename($_SERVER['PHP_SELF'])==='materials.php'    ?'class="active"':'' ?>><i class="fas fa-folder-open"></i> Study Materials</a>
  <a href="assessments.php"  <?= basename($_SERVER['PHP_SELF'])==='assessments.php'  ?'class="active"':'' ?>><i class="fas fa-tasks"></i> Assessments</a>
  <a href="submissions.php"  <?= basename($_SERVER['PHP_SELF'])==='submissions.php'  ?'class="active"':'' ?>><i class="fas fa-inbox"></i> Submissions</a>
  <div class="menu-title">Records</div>
  <a href="attendance.php"   <?= basename($_SERVER['PHP_SELF'])==='attendance.php'   ?'class="active"':'' ?>><i class="fas fa-user-check"></i> Attendance</a>
  <a href="grades.php"       <?= basename($_SERVER['PHP_SELF'])==='grades.php'       ?'class="active"':'' ?>><i class="fas fa-star"></i> Grades</a>
</aside>
<main class="content">
