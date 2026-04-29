<?php
/**
 * Secure file download proxy
 * Usage: /lms/download.php?type=material&file=filename.pdf
 *        /lms/download.php?type=submission&file=filename.pdf
 */
require_once 'config.php';

// Must be logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(403);
    die('Access denied. Please login first.');
}

$type     = isset($_GET['type']) ? $_GET['type'] : '';
$filename = isset($_GET['file']) ? basename($_GET['file']) : ''; // basename prevents directory traversal

if (empty($filename) || !in_array($type, ['material', 'submission'])) {
    http_response_code(400);
    die('Invalid request.');
}

// Validate filename — only safe chars allowed
if (!preg_match('/^[a-zA-Z0-9._\-]+$/', $filename)) {
    http_response_code(400);
    die('Invalid filename.');
}

if ($type === 'material') {
    $filepath = UPLOAD_MATERIALS_PATH . $filename;
    // Students and faculty can download materials — verify enrolled or owns course
    if ($_SESSION['role'] === 'student') {
        $sid   = (int)$_SESSION['user_id'];
        $check = mysqli_fetch_row(mysqli_query($conn,
            "SELECT m.id FROM materials m
             JOIN enrollments e ON m.course_id = e.course_id
             WHERE m.filename = '" . sanitize($conn, $filename) . "'
             AND e.student_id = $sid AND e.status = 'approved'"));
        if (!$check) { http_response_code(403); die('Access denied.'); }
    } elseif ($_SESSION['role'] === 'faculty') {
        $fid   = (int)$_SESSION['user_id'];
        $check = mysqli_fetch_row(mysqli_query($conn,
            "SELECT m.id FROM materials m
             JOIN courses c ON m.course_id = c.id
             WHERE m.filename = '" . sanitize($conn, $filename) . "'
             AND c.faculty_id = $fid"));
        if (!$check) { http_response_code(403); die('Access denied.'); }
    } elseif ($_SESSION['role'] !== 'admin') {
        http_response_code(403); die('Access denied.');
    }
} else {
    // submission
    $filepath = UPLOAD_SUBMISSIONS_PATH . $filename;
    if ($_SESSION['role'] === 'student') {
        $sid   = (int)$_SESSION['user_id'];
        $check = mysqli_fetch_row(mysqli_query($conn,
            "SELECT id FROM submissions WHERE filename = '" . sanitize($conn, $filename) . "' AND student_id = $sid"));
        if (!$check) { http_response_code(403); die('Access denied.'); }
    } elseif ($_SESSION['role'] === 'faculty') {
        $fid   = (int)$_SESSION['user_id'];
        $check = mysqli_fetch_row(mysqli_query($conn,
            "SELECT s.id FROM submissions s
             JOIN assessments a ON s.assessment_id = a.id
             JOIN courses c ON a.course_id = c.id
             WHERE s.filename = '" . sanitize($conn, $filename) . "'
             AND c.faculty_id = $fid"));
        if (!$check) { http_response_code(403); die('Access denied.'); }
    } elseif ($_SESSION['role'] !== 'admin') {
        http_response_code(403); die('Access denied.');
    }
}

// Check file exists on disk
if (!file_exists($filepath)) {
    http_response_code(404);
    die('File not found on server. It may have been deleted. Path checked: ' . htmlspecialchars($filepath));
}

// Detect MIME type
$ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$mime_map = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'txt'  => 'text/plain',
    'zip'  => 'application/zip',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
];
$mime = $mime_map[$ext] ?? 'application/octet-stream';

// Stream file to browser
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache');
ob_clean();
flush();
readfile($filepath);
exit;
?>
