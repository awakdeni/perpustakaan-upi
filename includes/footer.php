<footer class="bg-white border-t border-gray-200 mt-16">
    <div class="max-w-7xl mx-auto px-4 py-8">

        <!-- Top Footer -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">

            <!-- Brand -->
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-amber-500 text-white w-9 h-9 flex items-center justify-center rounded-lg">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <h3 class="font-bold text-gray-800">Perpustakaan UPI YPTK</h3>
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Sistem Informasi Perpustakaan Digital untuk mendukung kegiatan akademik dan pengelolaan data perpustakaan.
                </p>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Kontak</h4>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center gap-2">
                        <i class="fas fa-envelope text-amber-500"></i>
                        perpustakaan@upiyptk.ac.id
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-phone text-amber-500"></i>
                        (021) 1234-5678
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-amber-500"></i>
                        Jakarta, Indonesia
                    </li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Menu Cepat</h4>
                <ul class="grid grid-cols-2 gap-2 text-sm">
                    <li><a href="dashboard.php" class="text-gray-600 hover:text-amber-600">Dashboard</a></li>
                    <li><a href="buku.php" class="text-gray-600 hover:text-amber-600">Buku</a></li>
                    <li><a href="kategori.php" class="text-gray-600 hover:text-amber-600">Kategori</a></li>
                    <li><a href="peminjaman.php" class="text-gray-600 hover:text-amber-600">Peminjaman</a></li>
                    <li><a href="users.php" class="text-gray-600 hover:text-amber-600">Mahasiswa</a></li>
                </ul>
            </div>

        </div>

        <!-- Bottom Footer -->
        <div class="border-t pt-4 flex flex-col md:flex-row justify-between items-center gap-3">
            <p class="text-xs text-gray-500 text-center md:text-left">
                © <?= date('Y'); ?> <span class="font-semibold text-gray-700">Perpustakaan UPI YPTK</span>. All rights reserved.
            </p>

            <p class="text-xs text-gray-400 flex items-center gap-1">
                <i class="fas fa-code"></i>
                Developed by UPI YPTK Team
            </p>
        </div>

    </div>
</footer>
