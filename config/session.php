<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

// Check if user is logged in as student
function isStudentLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_nim']);
}

// Redirect if not logged in as admin
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ../login.php?type=admin');
        exit();
    }
}

// Redirect if not logged in as student
function requireStudentLogin() {
    if (!isStudentLoggedIn()) {
        header('Location: ../login.php?type=student');
        exit();
    }
}

// Get logged in admin data
function getAdminData() {
    if (isAdminLoggedIn()) {
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'nama_lengkap' => $_SESSION['admin_nama'] ?? '',
            'email' => $_SESSION['admin_email'] ?? ''
        ];
    }
    return null;
}
 
// Get logged in student data
function getStudentData() {
    if (isStudentLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'nim' => $_SESSION['user_nim'],
            'nama_lengkap' => $_SESSION['user_nama'] ?? '',
            'jurusan' => $_SESSION['user_jurusan'] ?? '',
            'email' => $_SESSION['user_email'] ?? ''
        ];
    }
    return null;
}
?>
