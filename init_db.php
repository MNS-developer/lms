<?php
/**
 * DB Initializer — run once to set up tables and demo data.
 * Visit:  https://your-app.railway.app/init_db.php
 * DELETE this file after running it.
 */
require_once 'config.php';

$queries = [
"CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",
"CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(150) NOT NULL,
    description TEXT,
    credits INT DEFAULT 3,
    faculty_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE SET NULL
)",
"CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
)",
"CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present','absent') DEFAULT 'present',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)",
"CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)",
"CREATE TABLE IF NOT EXISTS assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    type ENUM('assignment','quiz','project') DEFAULT 'assignment',
    description TEXT,
    deadline DATETIME,
    total_marks INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)",
"CREATE TABLE IF NOT EXISTS submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    student_id INT NOT NULL,
    filename VARCHAR(255),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (assessment_id, student_id)
)",
"CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    marks INT,
    grade VARCHAR(5),
    remarks TEXT,
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE
)",
"CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    day ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)",
];

$errors = [];
foreach ($queries as $sql) {
    if (!mysqli_query($conn, $sql)) {
        $errors[] = mysqli_error($conn);
    }
}

// Seed demo data (ignore duplicate errors)
$seeds = [
    "INSERT IGNORE INTO admins (name,email,password) VALUES ('Admin','admin@lms.com',MD5('admin123'))",
    "INSERT IGNORE INTO faculty (name,email,password,department) VALUES ('Dr. Ali Hassan','ali@lms.com',MD5('faculty123'),'Computer Science')",
    "INSERT IGNORE INTO faculty (name,email,password,department) VALUES ('Dr. Sara Khan','sara@lms.com',MD5('faculty123'),'Mathematics')",
    "INSERT IGNORE INTO students (roll_number,name,email,password,phone) VALUES ('CS-2021-001','Ahmed Raza','ahmed@lms.com',MD5('student123'),'03001234567')",
];
foreach ($seeds as $sql) { mysqli_query($conn, $sql); }

// Seed courses after faculty inserted
$f1 = mysqli_fetch_row(mysqli_query($conn, "SELECT id FROM faculty WHERE email='ali@lms.com'"));
$f2 = mysqli_fetch_row(mysqli_query($conn, "SELECT id FROM faculty WHERE email='sara@lms.com'"));
if ($f1) {
    mysqli_query($conn, "INSERT IGNORE INTO courses (course_code,course_name,credits,faculty_id) VALUES ('CS301','Database Systems',3,{$f1[0]})");
    mysqli_query($conn, "INSERT IGNORE INTO courses (course_code,course_name,credits,faculty_id) VALUES ('CS302','Web Engineering',3,{$f1[0]})");
}
if ($f2) {
    mysqli_query($conn, "INSERT IGNORE INTO courses (course_code,course_name,credits,faculty_id) VALUES ('MATH201','Calculus II',3,{$f2[0]})");
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>DB Init</title>
<style>body{font-family:Arial,sans-serif;max-width:600px;margin:60px auto;padding:20px;}
.ok{color:#155724;background:#d4edda;padding:14px;border-radius:6px;margin-bottom:12px;}
.err{color:#721c24;background:#f8d7da;padding:14px;border-radius:6px;margin-bottom:12px;}
.warn{color:#856404;background:#fff3cd;padding:14px;border-radius:6px;margin-top:20px;}
</style>
</head>
<body>
<h2>🛠 Database Initialization</h2>
<?php if (empty($errors)): ?>
    <div class="ok">✅ All tables created and demo data inserted successfully!</div>
    <p><strong>Demo Logins:</strong></p>
    <ul>
        <li>Admin: admin@lms.com / admin123</li>
        <li>Faculty: ali@lms.com / faculty123</li>
        <li>Student: ahmed@lms.com / student123</li>
    </ul>
    <p><a href="index.php">→ Go to Login Page</a></p>
    <div class="warn">⚠️ <strong>Security:</strong> Delete or rename this file (<code>init_db.php</code>) now to prevent anyone from re-running it.</div>
<?php else: ?>
    <div class="err">❌ Some errors occurred:</div>
    <ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
<?php endif; ?>
</body>
</html>
