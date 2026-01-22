<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Redirect jika sudah login
if (isStudentLoggedIn()) {
    header('Location: student/dashboard.php');
    exit();
}
if (isAdminLoggedIn()) {
    header('Location: admin/dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim          = trim($_POST['nim']);
    $nama         = trim($_POST['nama_lengkap']);
    $jurusan      = trim($_POST['jurusan']);
    $email        = trim($_POST['email']);
    $password     = $_POST['password'];
    $password2    = $_POST['password2'];

    if ($nim == '' || $nama == '' || $jurusan == '' || $email == '' || $password == '') {
        $error = 'Semua field wajib diisi!';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        $conn = getConnection();

        // Cek NIM / Email
        $stmt = $conn->prepare("SELECT id_user FROM users WHERE nim = ? OR email = ?");
        $stmt->bind_param("ss", $nim, $email);
        $stmt->execute();
        $check = $stmt->get_result();

        if ($check->num_rows > 0) {
            $error = 'NIM atau Email sudah terdaftar!';
        } else {
            $hash = md5($password);

            $stmt = $conn->prepare(
                "INSERT INTO users (nim, password, nama_lengkap, jurusan, email)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssss", $nim, $hash, $nama, $jurusan, $email);

            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Registrasi gagal, coba lagi.';
            }
        }

        $stmt->close();
        closeConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - Perpustakaan UPI YPTK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-amber-50 via-white to-yellow-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">

    <!-- Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-amber-400 to-yellow-500 rounded-3xl mb-4 shadow-xl">
            <i class="fas fa-user-plus text-white text-3xl"></i>
        </div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-amber-600 to-yellow-600 bg-clip-text text-transparent">
            Registrasi Mahasiswa
        </h1>
        <p class="text-gray-600 text-sm">Perpustakaan UPI YPTK</p>
    </div>

    <!-- Card -->
    <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-6 md:p-8 border border-gray-100">

        <!-- Error -->
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Success -->
        <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" class="space-y-5">

            <div>
                <label class="font-semibold text-sm text-gray-700">NIM</label>
                <input type="text" name="nim" required
                       class="w-full mt-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
            </div>

            <div>
                <label class="font-semibold text-sm text-gray-700">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required
                       class="w-full mt-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
            </div>

            <div>
                <label class="font-semibold text-sm text-gray-700">Jurusan</label>
                <input type="text" name="jurusan" required
                       class="w-full mt-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
            </div>

            <div>
                <label class="font-semibold text-sm text-gray-700">Email</label>
                <input type="email" name="email" required
                       class="w-full mt-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
            </div>

            <div>
                <label class="font-semibold text-sm text-gray-700">Password</label>
                <input type="password" name="password" required
                       class="w-full mt-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
            </div>

            <div>
                <label class="font-semibold text-sm text-gray-700">Konfirmasi Password</label>
                <input type="password" name="password2" required
                       class="w-full mt-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-amber-500 focus:ring-4 focus:ring-amber-100">
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600
                       text-white font-bold py-4 rounded-xl shadow-lg transition">
                <i class="fas fa-user-plus mr-2"></i> Daftar
            </button>
        </form>

        <!-- Login Link -->
        <div class="text-center mt-6">
            <p class="text-gray-600 text-sm">
                Sudah punya akun?
                <a href="login.php" class="text-amber-600 font-semibold hover:underline">
                    Login sekarang
                </a>
            </p>
        </div>
    </div>

    <p class="text-center text-gray-500 text-xs mt-6">
        &copy; <?= date('Y'); ?> Perpustakaan UPI YPTK
    </p>

</div>
</body>
</html>
