<?php
require_once 'config.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin')   redirect('admin/dashboard.php');
    if ($_SESSION['role'] === 'faculty') redirect('faculty/dashboard.php');
    if ($_SESSION['role'] === 'student') redirect('student/dashboard.php');
}

$error = '';
$role  = isset($_GET['role']) ? $_GET['role'] : 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role     = sanitize($conn, $_POST['role']);
    $email    = sanitize($conn, $_POST['email']);
    $password = md5($_POST['password']);

    if ($role === 'admin') {
        $sql = "SELECT * FROM admins WHERE email='$email' AND password='$password'";
    } elseif ($role === 'faculty') {
        $sql = "SELECT * FROM faculty WHERE email='$email' AND password='$password'";
    } else {
        $sql = "SELECT * FROM students WHERE email='$email' AND password='$password'";
    }

    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role']      = $role;
        if ($role === 'admin')   redirect('admin/dashboard.php');
        if ($role === 'faculty') redirect('faculty/dashboard.php');
        redirect('student/dashboard.php');
    } else {
        $error = 'Invalid email or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — Learnix LMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

body {
  font-family: 'Inter', system-ui, sans-serif;
  background: #f1f5f9;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

/* Subtle background pattern */
body::before {
  content: '';
  position: fixed; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 70% 50% at 30% 20%, rgba(37,99,235,0.06) 0%, transparent 60%),
    radial-gradient(ellipse 50% 40% at 75% 80%, rgba(124,58,237,0.05) 0%, transparent 60%);
}

.login-wrap {
  position: relative; z-index: 1;
  width: 100%; max-width: 420px;
}

/* Card */
.login-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0,0,0,.1), 0 2px 8px rgba(0,0,0,.06);
  overflow: hidden;
}

/* Top accent stripe */
.login-card::before {
  content: '';
  display: block; height: 4px;
  background: linear-gradient(90deg, #2563eb 0%, #7c3aed 50%, #2563eb 100%);
  background-size: 200% 100%;
  animation: stripe-slide 3s linear infinite;
}
@keyframes stripe-slide {
  0%   { background-position: 0% 0%; }
  100% { background-position: 200% 0%; }
}

.login-body { padding: 36px 36px 32px; }

/* Brand */
.brand {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 28px;
}
.brand-icon {
  width: 44px; height: 44px; border-radius: 11px;
  background: #2563eb;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 20px; flex-shrink: 0;
}
.brand-name {
  font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -.4px;
}
.brand-sub {
  font-size: 12px; color: #94a3b8; margin-top: 1px;
}

/* Heading */
.login-heading {
  font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 4px;
}
.login-sub {
  font-size: 13px; color: #64748b; margin-bottom: 24px;
}

/* Role tabs */
.role-tabs {
  display: flex; gap: 4px;
  background: #f1f5f9; border-radius: 9px; padding: 4px;
  margin-bottom: 24px;
}
.role-tab {
  flex: 1; text-align: center; padding: 8px 6px;
  border-radius: 6px; font-size: 12px; font-weight: 600;
  color: #64748b; cursor: pointer; border: none;
  background: transparent; font-family: inherit;
  transition: all 0.18s; text-decoration: none; display: block;
}
.role-tab:hover { color: #0f172a; text-decoration: none; }
.role-tab.active {
  background: #fff; color: #2563eb;
  box-shadow: 0 1px 4px rgba(0,0,0,.1);
}

/* Error */
.error-box {
  display: flex; align-items: center; gap: 8px;
  background: #fef2f2; border: 1px solid #fecaca;
  color: #dc2626; border-radius: 8px; padding: 10px 13px;
  font-size: 13px; margin-bottom: 18px;
}

/* Fields */
.field { margin-bottom: 16px; }
.field label {
  display: block; font-size: 12px; font-weight: 600;
  color: #374151; margin-bottom: 6px;
}
.input-wrap { position: relative; }
.input-wrap i.input-icon {
  position: absolute; left: 12px; top: 50%;
  transform: translateY(-50%);
  color: #94a3b8; font-size: 13px; pointer-events: none;
}
.input-wrap input {
  width: 100%; padding: 10px 12px 10px 36px;
  background: #f8fafc; border: 1px solid #e2e8f0;
  border-radius: 8px; font-size: 13px; font-family: inherit;
  color: #0f172a; outline: none;
  transition: border-color .18s, box-shadow .18s;
}
.input-wrap input:focus {
  border-color: #2563eb; background: #fff;
  box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.input-wrap input::placeholder { color: #c1c9d4; }

/* Password toggle */
.pw-toggle {
  position: absolute; right: 11px; top: 50%;
  transform: translateY(-50%);
  background: none; border: none; cursor: pointer;
  color: #94a3b8; font-size: 13px; padding: 2px 4px;
  transition: color .18s;
}
.pw-toggle:hover { color: #2563eb; }

/* Submit */
.btn-submit {
  width: 100%; padding: 11px;
  background: #2563eb; color: #fff;
  border: none; border-radius: 8px;
  font-size: 14px; font-weight: 600; font-family: inherit;
  cursor: pointer; margin-top: 6px;
  box-shadow: 0 4px 12px rgba(37,99,235,.3);
  transition: all .18s;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-submit:hover {
  background: #1d4ed8;
  box-shadow: 0 6px 18px rgba(37,99,235,.4);
  transform: translateY(-1px);
}
.btn-submit:active { transform: translateY(0); }

/* Footer */
.login-footer {
  padding: 14px 36px;
  background: #f8fafc;
  border-top: 1px solid #f1f5f9;
  text-align: center;
  font-size: 12px; color: #94a3b8;
}

@media(max-width:480px) {
  body { padding: 12px; }
  .login-body { padding: 28px 22px 24px; }
  .login-footer { padding: 12px 22px; }
}
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-body">

      <!-- Brand -->
      <div class="brand">
        <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
        <div>
          <div class="brand-name">Learnix</div>
          <div class="brand-sub">Smart Learning System</div>
        </div>
      </div>

      <div class="login-heading">Welcome back</div>
      <p class="login-sub">Sign in to continue to your portal</p>

      <!-- Role tabs -->
      <div class="role-tabs">
        <a href="?role=student" class="role-tab <?= $role==='student'?'active':'' ?>">
          <i class="fas fa-user-graduate"></i> Student
        </a>
        <a href="?role=faculty" class="role-tab <?= $role==='faculty'?'active':'' ?>">
          <i class="fas fa-chalkboard-teacher"></i> Faculty
        </a>
        <a href="?role=admin" class="role-tab <?= $role==='admin'?'active':'' ?>">
          <i class="fas fa-cog"></i> Admin
        </a>
      </div>

      <?php if ($error): ?>
      <div class="error-box"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" autocomplete="on">
        <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

        <div class="field">
          <label>Email address</label>
          <div class="input-wrap">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" name="email" placeholder="you@example.com" required autocomplete="email">
          </div>
        </div>

        <div class="field">
          <label>Password</label>
          <div class="input-wrap">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" name="password" id="pw" placeholder="Enter your password" required autocomplete="current-password">
            <button type="button" class="pw-toggle" onclick="togglePw(this)" aria-label="Toggle password">
              <i class="fas fa-eye" id="pw-icon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-submit">
          <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
      </form>
    </div>

    <div class="login-footer">
      &copy; <?= date('Y') ?> Learnix &middot;
    </div>
  </div>
</div>

<script>
function togglePw(btn) {
  const inp  = document.getElementById('pw');
  const icon = document.getElementById('pw-icon');
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.className = 'fas fa-eye-slash';
  } else {
    inp.type = 'password';
    icon.className = 'fas fa-eye';
  }
}
</script>
</body>
</html>
