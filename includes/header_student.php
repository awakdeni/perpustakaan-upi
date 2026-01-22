<?php
require_once '../config/session.php';
requireStudentLogin();
$student = getStudentData();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Perpustakaan UPI YPTK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --yellow-primary: #FCD34D;
            --yellow-dark: #F59E0B;
            --yellow-light: #FEF3C7;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Student Header -->
    <header class="bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo and Title -->
                <div class="flex items-center space-x-3">
                    <i class="fas fa-book-reader text-white text-2xl"></i>
                    <div>
                        <h1 class="text-white font-bold text-lg">Perpustakaan UPI YPTK</h1>
                        <p class="text-yellow-100 text-xs">Portal Mahasiswa</p>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="dashboard.php" class="px-4 py-2 text-white hover:bg-yellow-600 rounded-lg transition duration-200 <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'bg-yellow-600' : ''; ?>">
                        <i class="fas fa-home mr-2"></i>Beranda
                    </a>
                    <a href="buku.php" class="px-4 py-2 text-white hover:bg-yellow-600 rounded-lg transition duration-200 <?php echo (basename($_SERVER['PHP_SELF']) == 'buku.php') ? 'bg-yellow-600' : ''; ?>">
                        <i class="fas fa-book mr-2"></i>Katalog Buku
                    </a>
                    <a href="peminjaman_saya.php" class="px-4 py-2 text-white hover:bg-yellow-600 rounded-lg transition duration-200 <?php echo (basename($_SERVER['PHP_SELF']) == 'peminjaman_saya.php') ? 'bg-yellow-600' : ''; ?>">
                        <i class="fas fa-list-alt mr-2"></i>Peminjaman Saya
                    </a>
                </nav>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="hidden md:block text-right">
                        <p class="text-white font-semibold text-sm"><?php echo htmlspecialchars($student['nama_lengkap']); ?></p>
                        <p class="text-yellow-100 text-xs">NIM: <?php echo htmlspecialchars($student['nim']); ?></p>
                    </div>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 bg-yellow-700 hover:bg-yellow-800 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-user-circle text-xl"></i>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-yellow-50 rounded-t-lg">
                                <i class="fas fa-user mr-2"></i>Profil
                            </a>
                            <a href="../logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 rounded-b-lg">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden text-white text-2xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-yellow-600 border-t border-yellow-700">
            <div class="container mx-auto px-4 py-3 space-y-2">
                <a href="dashboard.php" class="block px-4 py-2 text-white hover:bg-yellow-700 rounded-lg">
                    <i class="fas fa-home mr-2"></i>Beranda
                </a>
                <a href="buku.php" class="block px-4 py-2 text-white hover:bg-yellow-700 rounded-lg">
                    <i class="fas fa-book mr-2"></i>Katalog Buku
                </a>
                <a href="peminjaman_saya.php" class="block px-4 py-2 text-white hover:bg-yellow-700 rounded-lg">
                    <i class="fas fa-list-alt mr-2"></i>Peminjaman Saya
                </a>
                <a href="../logout.php" class="block px-4 py-2 text-red-200 hover:bg-red-600 rounded-lg">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });
    </script>
