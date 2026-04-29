<?php
// ─── Database ────────────────────────────────────────────────────────────────
$db_host = getenv('MYSQLHOST')     ?: getenv('DB_HOST') ?: 'localhost';
$db_port = getenv('MYSQLPORT')     ?: getenv('DB_PORT') ?: '3306';
$db_user = getenv('MYSQLUSER')     ?: getenv('DB_USER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'student_management_system';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, (int)$db_port);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// ─── Session ─────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Upload paths ─────────────────────────────────────────────────────────────
// Absolute path to the lms/ root folder on disk
define('LMS_ROOT', dirname(__FILE__));

// Full disk paths for move_uploaded_file()
define('UPLOAD_MATERIALS_PATH',   LMS_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'materials' . DIRECTORY_SEPARATOR);
define('UPLOAD_SUBMISSIONS_PATH', LMS_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'submissions' . DIRECTORY_SEPARATOR);

// Web URL base (e.g. /lms) — used for download links
function lms_base_url() {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $base   = preg_replace('#/(admin|faculty|student)/[^/]*$#', '', $script);
    $base   = preg_replace('#/[^/]*\.php$#', '', $base);
    return rtrim($base, '/');
}
if (!defined('LMS_BASE')) {
    define('LMS_BASE', lms_base_url());
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn($role) {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === $role;
}

function requireLogin($role, $redir) {
    if (!isLoggedIn($role)) {
        redirect($redir);
    }
}

function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Handle file upload — returns filename string on success, false on failure (sets $error)
function handle_upload($file_key, $dest_dir, &$error,
    $allowed = ['pdf','doc','docx','ppt','pptx','txt','zip','jpg','jpeg','png']) {

    $code_map = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds upload_max_filesize in php.ini',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds MAX_FILE_SIZE in form',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE    => 'No file was selected',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION  => 'Upload stopped by PHP extension',
    ];

    if (!isset($_FILES[$file_key])) {
        $error = 'File input not found.';
        return false;
    }
    $err_code = $_FILES[$file_key]['error'];
    if ($err_code !== UPLOAD_ERR_OK) {
        $error = $code_map[$err_code] ?? 'Unknown upload error (code ' . $err_code . ')';
        return false;
    }

    $orig = basename($_FILES[$file_key]['name']);
    $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        $error = 'File type ".' . htmlspecialchars($ext) . '" not allowed. Allowed: ' . implode(', ', $allowed);
        return false;
    }

    if (!is_dir($dest_dir)) {
        if (!mkdir($dest_dir, 0755, true)) {
            $error = 'Cannot create upload directory: ' . htmlspecialchars($dest_dir);
            return false;
        }
    }
    if (!is_writable($dest_dir)) {
        $error = 'Upload directory is not writable: ' . htmlspecialchars($dest_dir) .
                 ' — right-click the uploads/ folder in XAMPP and give write permission.';
        return false;
    }

    $safe_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig);
    if (!move_uploaded_file($_FILES[$file_key]['tmp_name'], $dest_dir . $safe_name)) {
        $error = 'move_uploaded_file() failed. Destination: ' . htmlspecialchars($dest_dir);
        return false;
    }
    return $safe_name;
}
?>
