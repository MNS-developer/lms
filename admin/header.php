<?php requireLogin('admin', '../index.php'); ?>
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
      <span style="color:var(--text-3);font-weight:400;font-size:13px;">Admin</span>
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
  <div class="menu-title">Manage</div>
  <a href="students.php"     <?= basename($_SERVER['PHP_SELF'])==='students.php'     ?'class="active"':'' ?>><i class="fas fa-user-graduate"></i> Students</a>
  <a href="faculty.php"      <?= basename($_SERVER['PHP_SELF'])==='faculty.php'      ?'class="active"':'' ?>><i class="fas fa-chalkboard-teacher"></i> Faculty</a>
  <a href="courses.php"      <?= basename($_SERVER['PHP_SELF'])==='courses.php'      ?'class="active"':'' ?>><i class="fas fa-book"></i> Courses</a>
  <a href="enrollments.php"  <?= basename($_SERVER['PHP_SELF'])==='enrollments.php'  ?'class="active"':'' ?>><i class="fas fa-clipboard-list"></i> Enrollments</a>
  <a href="timetable.php"    <?= basename($_SERVER['PHP_SELF'])==='timetable.php'    ?'class="active"':'' ?>><i class="fas fa-calendar-alt"></i> Timetable</a>
  <div class="menu-title">Communication</div>
  <a href="announcements.php"<?= basename($_SERVER['PHP_SELF'])==='announcements.php'?'class="active"':'' ?>><i class="fas fa-bullhorn"></i> Announcements</a>
</aside>
<main class="content">
