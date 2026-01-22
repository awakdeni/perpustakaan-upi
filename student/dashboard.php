<?php
$page_title = 'Dashboard';
require_once '../includes/header_student.php';
require_once '../config/database.php';

$conn = getConnection();
$student = getStudentData();

// Get student statistics
$stats = [];

// Active loans
$result = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE id_user = " . $student['id'] . " AND status = 'dipinjam'");
$stats['peminjaman_aktif'] = $result->fetch_assoc()['total'];

// Total loans
$result = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE id_user = " . $student['id']);
$stats['total_peminjaman'] = $result->fetch_assoc()['total'];

// Overdue loans
$result = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE id_user = " . $student['id'] . " AND status = 'terlambat'");
$stats['terlambat'] = $result->fetch_assoc()['total'];

// Recent loans
$recent_loans = $conn->query("
    SELECT p.*, b.judul_buku, b.kode_buku, b.foto_buku, k.nama_kategori,
           DATEDIFF(CURDATE(), p.tanggal_kembali) as hari_terlambat
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id_buku
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
    WHERE p.id_user = " . $student['id'] . "
    ORDER BY p.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Popular books
$popular_books = $conn->query("
    SELECT b.*, k.nama_kategori, COUNT(p.id_peminjaman) as jumlah_pinjam
    FROM buku b
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
    LEFT JOIN peminjaman p ON b.id_buku = p.id_buku
    WHERE b.stok > 0
    GROUP BY b.id_buku
    ORDER BY jumlah_pinjam DESC, b.judul_buku
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<div class="container mx-auto px-4 py-6 md:py-8 max-w-7xl">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-br from-amber-400 via-amber-500 to-yellow-500 rounded-2xl shadow-xl p-6 md:p-8 mb-6 md:mb-8 text-white relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-10 rounded-full -ml-12 -mb-12"></div>
        
        <div class="relative z-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h2 class="text-2xl md:text-3xl font-bold mb-2 flex items-center">
                        <i class="fas fa-book-reader mr-3"></i>
                        Selamat Datang!
                    </h2>
                    <p class="text-amber-50 text-base md:text-lg font-medium">
                        <?php echo htmlspecialchars($student['nama_lengkap']); ?>
                    </p>
                    <p class="text-amber-100 text-sm mt-1">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        <?php echo htmlspecialchars($student['jurusan'] ?: 'Mahasiswa'); ?>
                    </p>
                </div>
                <div class="flex items-center space-x-2 text-amber-50">
                    <i class="fas fa-calendar-alt text-lg"></i>
                    <span class="text-sm font-medium"><?php echo date('l, d F Y'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Active Loans Card -->
        <div class="group bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-gray-100 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-gray-500 text-sm font-semibold uppercase tracking-wide mb-2">Peminjaman Aktif</p>
                    <p class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent">
                        <?php echo $stats['peminjaman_aktif']; ?>
                    </p>
                    <p class="text-gray-400 text-xs mt-1">Buku sedang dipinjam</p>
                </div>
                <div class="bg-gradient-to-br from-blue-100 to-blue-50 p-4 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-book-open text-blue-600 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Loans Card -->
        <div class="group bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-gray-100 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-gray-500 text-sm font-semibold uppercase tracking-wide mb-2">Total Peminjaman</p>
                    <p class="text-4xl font-bold bg-gradient-to-r from-green-600 to-green-400 bg-clip-text text-transparent">
                        <?php echo $stats['total_peminjaman']; ?>
                    </p>
                    <p class="text-gray-400 text-xs mt-1">Sepanjang waktu</p>
                </div>
                <div class="bg-gradient-to-br from-green-100 to-green-50 p-4 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-list-alt text-green-600 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Late Returns Card -->
        <div class="group bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 p-6 border border-gray-100 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-gray-500 text-sm font-semibold uppercase tracking-wide mb-2">Terlambat</p>
                    <p class="text-4xl font-bold bg-gradient-to-r from-red-600 to-red-400 bg-clip-text text-transparent">
                        <?php echo $stats['terlambat']; ?>
                    </p>
                    <p class="text-gray-400 text-xs mt-1">Perlu dikembalikan</p>
                </div>
                <div class="bg-gradient-to-br from-red-100 to-red-50 p-4 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Warning Alert -->
    <?php if ($stats['terlambat'] > 0): ?>
    <div class="bg-gradient-to-r from-red-50 to-orange-50 border-l-4 border-red-500 p-4 md:p-6 rounded-xl mb-6 md:mb-8 shadow-md">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="bg-red-500 p-3 rounded-full">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                </div>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-red-800 text-base md:text-lg mb-1">
                    Peringatan Keterlambatan!
                </h4>
                <p class="text-red-700 text-sm md:text-base">
                    Anda memiliki <span class="font-bold"><?php echo $stats['terlambat']; ?> buku</span> yang terlambat dikembalikan.
                </p>
                <p class="text-red-600 text-xs md:text-sm mt-1">
                    Segera kembalikan untuk menghindari denda dan sanksi akademik.
                </p>
            </div>
            <a href="peminjaman_saya.php" class="flex-shrink-0 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors duration-300 hidden md:block">
                Lihat Detail
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
        
        <!-- Recent Loans Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 md:p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg md:text-xl font-bold text-gray-800 flex items-center">
                        <div class="bg-amber-500 p-2 rounded-lg mr-3">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        Peminjaman Terbaru
                    </h3>
                    <a href="peminjaman_saya.php" class="group flex items-center space-x-1 text-amber-600 hover:text-amber-700 text-sm font-semibold transition-colors duration-300">
                        <span>Lihat Semua</span>
                        <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform duration-300"></i>
                    </a>
                </div>
            </div>

            <!-- Content -->
            <div class="p-4 md:p-6">
                <div class="space-y-4">
                    <?php if (count($recent_loans) > 0): ?>
                        <?php foreach ($recent_loans as $loan): ?>
                            <div class="group border-l-4 <?php echo $loan['status'] == 'dipinjam' ? 'border-blue-500 bg-blue-50' : ($loan['status'] == 'terlambat' ? 'border-red-500 bg-red-50' : 'border-green-500 bg-green-50'); ?> p-4 rounded-r-xl hover:shadow-md transition-all duration-300">
                                <div class="flex gap-4">
                                    <!-- Book Cover -->
                                    <div class="flex-shrink-0">
                                        <?php if ($loan['foto_buku'] && file_exists('../uploads/buku/' . $loan['foto_buku'])): ?>
                                            <img src="../uploads/buku/<?= htmlspecialchars($loan['foto_buku']); ?>" 
                                                 alt="<?= htmlspecialchars($loan['judul_buku']); ?>" 
                                                 class="w-16 h-20 md:w-20 md:h-24 object-cover rounded-lg shadow-md border-2 border-white">
                                        <?php else: ?>
                                            <div class="w-16 h-20 md:w-20 md:h-24 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center shadow-md border-2 border-white">
                                                <i class="fas fa-book text-blue-500 text-2xl"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Book Info -->
                                    <div class="flex-1 min-w-0 flex justify-between items-start gap-3">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-gray-800 text-sm md:text-base mb-2 truncate group-hover:text-amber-600 transition-colors">
                                                <?php echo htmlspecialchars($loan['judul_buku']); ?>
                                            </h4>
                                            
                                            <div class="space-y-1.5">
                                                <p class="text-gray-600 text-xs md:text-sm flex items-center">
                                                    <i class="fas fa-calendar-alt text-amber-500 mr-2"></i>
                                                    Pinjam: <span class="font-medium ml-1"><?php echo date('d/m/Y', strtotime($loan['tanggal_pinjam'])); ?></span>
                                                </p>
                                                <p class="text-gray-600 text-xs md:text-sm flex items-center">
                                                    <i class="fas fa-calendar-check text-amber-500 mr-2"></i>
                                                    Kembali: <span class="font-medium ml-1"><?php echo date('d/m/Y', strtotime($loan['tanggal_kembali'])); ?></span>
                                                </p>
                                            </div>

                                            <?php if ($loan['status'] == 'dipinjam' && $loan['hari_terlambat'] > 0): ?>
                                                <div class="mt-2 flex items-center space-x-1 text-red-600 text-xs md:text-sm font-semibold">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <span>Terlambat <?php echo $loan['hari_terlambat']; ?> hari</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <span class="flex-shrink-0 px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap <?php 
                                            echo $loan['status'] == 'dipinjam' ? 'bg-blue-500 text-white' : 
                                                ($loan['status'] == 'terlambat' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'); 
                                        ?>">
                                            <?php 
                                            echo $loan['status'] == 'dipinjam' ? 'Dipinjam' : 
                                                ($loan['status'] == 'terlambat' ? 'Terlambat' : 'Kembali'); 
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                                <i class="fas fa-book text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">Belum ada peminjaman</p>
                            <p class="text-gray-400 text-sm mt-1">Mulai pinjam buku favorit Anda</p>
                            <a href="buku.php" class="inline-block mt-4 bg-amber-500 hover:bg-amber-600 text-white px-6 py-2 rounded-lg text-sm font-semibold transition-colors duration-300">
                                Jelajahi Buku
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Popular Books Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 md:p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg md:text-xl font-bold text-gray-800 flex items-center">
                        <div class="bg-orange-500 p-2 rounded-lg mr-3">
                            <i class="fas fa-fire text-white"></i>
                        </div>
                        Buku Populer
                    </h3>
                    <a href="buku.php" class="group flex items-center space-x-1 text-amber-600 hover:text-amber-700 text-sm font-semibold transition-colors duration-300">
                        <span>Lihat Semua</span>
                        <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform duration-300"></i>
                    </a>
                </div>
            </div>

            <!-- Content -->
            <div class="p-4 md:p-6">
                <div class="space-y-3">
                    <?php if (count($popular_books) > 0): ?>
                        <?php foreach ($popular_books as $book): ?>
                            <div class="group border-2 border-gray-100 hover:border-amber-300 rounded-xl p-4 hover:shadow-lg transition-all duration-300 cursor-pointer bg-gradient-to-r from-white to-gray-50">
                                <div class="flex gap-4">
                                    <!-- Book Cover -->
                                    <div class="flex-shrink-0">
                                        <?php if ($book['foto_buku'] && file_exists('../uploads/buku/' . $book['foto_buku'])): ?>
                                            <img src="../uploads/buku/<?= htmlspecialchars($book['foto_buku']); ?>" 
                                                 alt="<?= htmlspecialchars($book['judul_buku']); ?>" 
                                                 class="w-16 h-20 md:w-20 md:h-24 object-cover rounded-lg shadow-md border-2 border-white group-hover:scale-105 transition-transform duration-300">
                                        <?php else: ?>
                                            <div class="w-16 h-20 md:w-20 md:h-24 bg-gradient-to-br from-amber-100 to-orange-200 rounded-lg flex items-center justify-center shadow-md border-2 border-white group-hover:scale-105 transition-transform duration-300">
                                                <i class="fas fa-book text-amber-600 text-2xl"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Book Info -->
                                    <div class="flex-1 min-w-0 flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-gray-800 text-sm md:text-base mb-1 truncate group-hover:text-amber-600 transition-colors">
                                                <?php echo htmlspecialchars($book['judul_buku']); ?>
                                            </h4>
                                            <p class="text-gray-500 text-xs md:text-sm flex items-center mb-3">
                                                <i class="fas fa-user-edit text-amber-500 mr-2"></i>
                                                <?php echo htmlspecialchars($book['pengarang']); ?>
                                            </p>
                                            
                                            <div class="flex items-center justify-between">
                                                <span class="px-3 py-1 bg-gradient-to-r from-amber-100 to-yellow-100 text-amber-800 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-tag mr-1"></i>
                                                    <?php echo htmlspecialchars($book['nama_kategori'] ?: 'Umum'); ?>
                                                </span>
                                                
                                                <div class="flex items-center space-x-1">
                                                    <?php if ($book['stok'] > 5): ?>
                                                        <span class="text-green-600 text-xs font-semibold flex items-center">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            Tersedia (<?php echo $book['stok']; ?>)
                                                        </span>
                                                    <?php elseif ($book['stok'] > 0): ?>
                                                        <span class="text-orange-600 text-xs font-semibold flex items-center">
                                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                                            Terbatas (<?php echo $book['stok']; ?>)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-red-600 text-xs font-semibold flex items-center">
                                                            <i class="fas fa-times-circle mr-1"></i>
                                                            Habis
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                                <i class="fas fa-book-open text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">Tidak ada buku tersedia</p>
                            <p class="text-gray-400 text-sm mt-1">Silakan cek kembali nanti</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 md:mt-8 grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        <a href="buku.php" class="group bg-white hover:bg-amber-50 border-2 border-gray-200 hover:border-amber-300 rounded-xl p-4 text-center transition-all duration-300 hover:shadow-lg">
            <div class="bg-amber-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-search text-amber-600 text-xl"></i>
            </div>
            <p class="text-gray-700 font-semibold text-sm">Cari Buku</p>
        </a>

        <a href="peminjaman_saya.php" class="group bg-white hover:bg-blue-50 border-2 border-gray-200 hover:border-blue-300 rounded-xl p-4 text-center transition-all duration-300 hover:shadow-lg">
            <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-history text-blue-600 text-xl"></i>
            </div>
            <p class="text-gray-700 font-semibold text-sm">Riwayat</p>
        </a>

        <a href="profil.php" class="group bg-white hover:bg-green-50 border-2 border-gray-200 hover:border-green-300 rounded-xl p-4 text-center transition-all duration-300 hover:shadow-lg">
            <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-user text-green-600 text-xl"></i>
            </div>
            <p class="text-gray-700 font-semibold text-sm">Profil</p>
        </a>

        <a href="bantuan.php" class="group bg-white hover:bg-purple-50 border-2 border-gray-200 hover:border-purple-300 rounded-xl p-4 text-center transition-all duration-300 hover:shadow-lg">
            <div class="bg-purple-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-question-circle text-purple-600 text-xl"></i>
            </div>
            <p class="text-gray-700 font-semibold text-sm">Bantuan</p>
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
