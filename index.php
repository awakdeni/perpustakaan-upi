<?php
require_once 'config/session.php';

// Redirect if logged in
if (isAdminLoggedIn()) {
    header('Location: admin/dashboard.php');
    exit();
}
if (isStudentLoggedIn()) {
    header('Location: student/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan UPI YPTK - Sistem Informasi Perpustakaan Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'fade-in': 'fadeIn 0.5s ease-in',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-amber-50 via-white to-yellow-50 min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-amber-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 md:h-20">
                <!-- Logo -->
                <div class="flex items-center space-x-3 group">
                    <div class="bg-gradient-to-br from-amber-400 to-yellow-500 p-2.5 rounded-xl shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                        <i class="fas fa-book-reader text-white text-xl"></i>
                    </div>
                    <div class="hidden sm:block">
                        <h1 class="text-lg md:text-xl font-bold bg-gradient-to-r from-amber-600 to-yellow-600 bg-clip-text text-transparent">
                            Perpustakaan UPI YPTK
                        </h1>
                        <p class="text-xs text-gray-500 hidden md:block">Sistem Informasi Digital</p>
                    </div>
                </div>

                <!-- Login Button -->
                <a href="login.php" class="group relative bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white px-6 py-2.5 rounded-xl font-semibold transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <span class="flex items-center space-x-2">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk</span>
                    </span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-12 md:py-20 lg:py-28">
        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 w-72 h-72 bg-amber-200 rounded-full filter blur-3xl opacity-20 animate-float"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-yellow-200 rounded-full filter blur-3xl opacity-20 animate-float" style="animation-delay: 1s;"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center animate-fade-in">
                <!-- Icon -->
                <div class="inline-flex items-center justify-center w-20 h-20 md:w-24 md:h-24 bg-gradient-to-br from-amber-400 to-yellow-500 rounded-3xl shadow-2xl mb-8 animate-float">
                    <i class="fas fa-book-reader text-white text-3xl md:text-4xl"></i>
                </div>

                <!-- Heading -->
                <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-6 leading-tight">
                    Selamat Datang di
                    <span class="bg-gradient-to-r from-amber-600 to-yellow-600 bg-clip-text text-transparent block mt-2">
                        Perpustakaan Digital
                    </span>
                </h2>

                <!-- Subtitle -->
                <p class="text-base sm:text-lg md:text-xl text-gray-600 max-w-3xl mx-auto mb-10 px-4 leading-relaxed">
                    Akses ribuan koleksi buku digital untuk mendukung kegiatan akademik dan penelitian Anda kapan saja, di mana saja
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="login.php" class="w-full sm:w-auto group bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white px-8 py-4 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <span class="flex items-center justify-center space-x-2">
                            <span>Mulai Sekarang</span>
                            <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </span>
                    </a>
                    <a href="#features" class="w-full sm:w-auto bg-white hover:bg-gray-50 text-gray-800 px-8 py-4 rounded-xl font-semibold transition-all duration-300 shadow-md hover:shadow-lg border border-gray-200">
                        <span class="flex items-center justify-center space-x-2">
                            <span>Pelajari Lebih Lanjut</span>
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Fitur Unggulan
                </h3>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Nikmati berbagai kemudahan dalam mengakses koleksi perpustakaan digital kami
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <!-- Feature 1 -->
                <div class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-amber-200 transform hover:-translate-y-2">
                    <div class="bg-gradient-to-br from-amber-100 to-yellow-100 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-book text-amber-600 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Koleksi Lengkap</h4>
                    <p class="text-gray-600 leading-relaxed">
                        Akses ribuan buku dari berbagai kategori dan bidang ilmu untuk mendukung pembelajaran Anda
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-amber-200 transform hover:-translate-y-2">
                    <div class="bg-gradient-to-br from-amber-100 to-yellow-100 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-search text-amber-600 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Pencarian Cepat</h4>
                    <p class="text-gray-600 leading-relaxed">
                        Temukan buku yang Anda cari dengan sistem pencarian yang canggih dan mudah digunakan
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-amber-200 transform hover:-translate-y-2">
                    <div class="bg-gradient-to-br from-amber-100 to-yellow-100 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-clock text-amber-600 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Akses 24/7</h4>
                    <p class="text-gray-600 leading-relaxed">
                        Akses perpustakaan kapan saja dan di mana saja tanpa batasan waktu dan lokasi
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-amber-200 transform hover:-translate-y-2">
                    <div class="bg-gradient-to-br from-amber-100 to-yellow-100 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-mobile-alt text-amber-600 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Mobile Friendly</h4>
                    <p class="text-gray-600 leading-relaxed">
                        Tampilan responsif yang optimal di berbagai perangkat, dari smartphone hingga desktop
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-amber-200 transform hover:-translate-y-2">
                    <div class="bg-gradient-to-br from-amber-100 to-yellow-100 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-bookmark text-amber-600 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Bookmark</h4>
                    <p class="text-gray-600 leading-relaxed">
                        Simpan dan tandai buku favorit Anda untuk akses cepat di kemudian hari
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 p-8 border border-gray-100 hover:border-amber-200 transform hover:-translate-y-2">
                    <div class="bg-gradient-to-br from-amber-100 to-yellow-100 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-history text-amber-600 text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Riwayat Peminjaman</h4>
                    <p class="text-gray-600 leading-relaxed">
                        Pantau riwayat peminjaman buku dan status pengembalian dengan mudah
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 md:py-20 bg-gradient-to-r from-amber-500 to-yellow-500 relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-5"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h3 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Siap Memulai Perjalanan Literasi Anda?
            </h3>
            <p class="text-lg md:text-xl text-amber-50 mb-8 max-w-2xl mx-auto">
                Bergabunglah dengan ribuan pengguna lainnya dan nikmati akses ke koleksi buku digital terlengkap
            </p>
            <a href="login.php" class="inline-block bg-white text-amber-600 hover:bg-amber-50 px-8 py-4 rounded-xl font-bold transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                <span class="flex items-center space-x-2">
                    <span>Mulai Sekarang</span>
                    <i class="fas fa-arrow-right"></i>
                </span>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12">
                <!-- About -->
                <div class="lg:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="bg-gradient-to-br from-amber-400 to-yellow-500 p-2.5 rounded-xl shadow-lg">
                            <i class="fas fa-book-reader text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white">Perpustakaan UPI YPTK</h3>
                    </div>
                    <p class="text-gray-400 leading-relaxed mb-4">
                        Sistem Informasi Perpustakaan Digital yang menyediakan akses mudah dan cepat ke berbagai koleksi buku untuk mendukung kegiatan akademik dan penelitian.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-amber-500 rounded-lg flex items-center justify-center transition-colors duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-amber-500 rounded-lg flex items-center justify-center transition-colors duration-300">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-amber-500 rounded-lg flex items-center justify-center transition-colors duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-4">Kontak</h4>
                    <ul class="space-y-3">
                        <li class="flex items-start space-x-3">
                            <i class="fas fa-envelope text-amber-500 mt-1"></i>
                            <span class="text-gray-400">perpustakaan@upiyptk.ac.id</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <i class="fas fa-phone text-amber-500 mt-1"></i>
                            <span class="text-gray-400">(021) 1234-5678</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <i class="fas fa-map-marker-alt text-amber-500 mt-1"></i>
                            <span class="text-gray-400">Jl. Lubuk Begalung, Padang</span>
                        </li>
                    </ul>
                </div>

                <!-- Hours -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-4">Jam Operasional</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex justify-between">
                            <span>Senin - Jumat</span>
                            <span class="text-amber-500">08:00 - 17:00</span>
                        </li>
                        <li class="flex justify-between">
                            <span>Sabtu</span>
                            <span class="text-amber-500">08:00 - 14:00</span>
                        </li>
                        <li class="flex justify-between">
                            <span>Minggu</span>
                            <span class="text-red-400">Tutup</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-500 text-sm">
                    &copy; <?php echo date('Y'); ?> Perpustakaan Universitas UPI YPTK. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>