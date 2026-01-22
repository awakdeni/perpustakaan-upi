<?php
$page_title = 'Dashboard';
require_once '../includes/header_admin.php';
require_once '../config/database.php';

$conn = getConnection();

// Get statistics
$stats = [];
$stats['total_buku'] = $conn->query("SELECT COUNT(*) as total FROM buku")->fetch_assoc()['total'];
$stats['total_kategori'] = $conn->query("SELECT COUNT(*) as total FROM kategori")->fetch_assoc()['total'];
$stats['total_mahasiswa'] = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$stats['peminjaman_aktif'] = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'")->fetch_assoc()['total'];
$stats['terlambat'] = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'terlambat'")->fetch_assoc()['total'];

$recent_loans = $conn->query("
    SELECT p.*, u.nama_lengkap, u.nim, b.judul_buku 
    FROM peminjaman p
    JOIN users u ON p.id_user = u.id_user
    JOIN buku b ON p.id_buku = b.id_buku
    ORDER BY p.created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

$low_stock = $conn->query("
    SELECT * FROM buku WHERE stok < 5 ORDER BY stok ASC LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Dashboard</h1>
            <p class="text-sm sm:text-base text-gray-600">
                Selamat datang, <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($admin['nama_lengkap']); ?></span>
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6 mb-6 sm:mb-8">
            <?php
            $cards = [
                ['title' => 'Total Buku', 'icon' => 'book', 'color' => 'blue', 'value' => $stats['total_buku'], 'bg' => 'from-blue-500 to-blue-600'],
                ['title' => 'Kategori', 'icon' => 'tags', 'color' => 'purple', 'value' => $stats['total_kategori'], 'bg' => 'from-purple-500 to-purple-600'],
                ['title' => 'Mahasiswa', 'icon' => 'users', 'color' => 'green', 'value' => $stats['total_mahasiswa'], 'bg' => 'from-green-500 to-green-600'],
                ['title' => 'Dipinjam', 'icon' => 'exchange-alt', 'color' => 'amber', 'value' => $stats['peminjaman_aktif'], 'bg' => 'from-amber-500 to-amber-600'],
            ];
            foreach ($cards as $c): 
                $colorClasses = [
                    'blue' => 'bg-blue-50 text-blue-600 border-blue-100',
                    'purple' => 'bg-purple-50 text-purple-600 border-purple-100',
                    'green' => 'bg-green-50 text-green-600 border-green-100',
                    'amber' => 'bg-amber-50 text-amber-600 border-amber-100'
                ];
            ?>
                <div class="group bg-white rounded-xl border border-gray-200 p-4 sm:p-6 hover:shadow-lg hover:border-gray-300 transition-all duration-300 relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute top-0 right-0 w-24 h-24 opacity-5 transform rotate-12 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-<?= $c['icon']; ?> text-7xl text-gray-900"></i>
                    </div>
                    
                    <div class="relative">
                        <!-- Icon -->
                        <div class="mb-4">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-gradient-to-br <?= $c['bg']; ?> flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-<?= $c['icon']; ?> text-white text-xl sm:text-2xl"></i>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div>
                            <p class="text-xs sm:text-sm font-medium text-gray-600 mb-2"><?= $c['title']; ?></p>
                            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 group-hover:text-<?= $c['color']; ?>-600 transition-colors">
                                <?= $c['value']; ?>
                            </h2>
                        </div>
                    </div>
                    
                    <!-- Bottom Accent -->
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r <?= $c['bg']; ?> transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Warning Alert -->
        <?php if ($stats['terlambat'] > 0): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 sm:mb-8">
            <div class="flex items-start sm:items-center justify-between gap-3 flex-wrap">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-red-900 mb-1">Peminjaman Terlambat</h3>
                        <p class="text-xs sm:text-sm text-red-700">
                            Terdapat <span class="font-bold"><?= $stats['terlambat']; ?></span> peminjaman yang belum dikembalikan
                        </p>
                    </div>
                </div>
                <a href="peminjaman.php?filter=terlambat" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs sm:text-sm font-medium transition flex-shrink-0">
                    Lihat Detail
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tables Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            
            <!-- Recent Loans -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-clock text-blue-600"></i>
                        <span class="hidden sm:inline">Peminjaman Terbaru</span>
                        <span class="sm:hidden">Terbaru</span>
                    </h3>
                    <a href="peminjaman.php" class="text-xs sm:text-sm text-blue-600 hover:text-blue-700 font-medium">
                        Lihat Semua →
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead class="bg-gray-50 text-gray-700 text-xs">
                            <tr>
                                <th class="px-3 sm:px-4 py-3 text-left font-medium">Mahasiswa</th>
                                <th class="px-3 sm:px-4 py-3 text-left font-medium">Buku</th>
                                <th class="px-3 sm:px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (count($recent_loans) > 0): ?>
                                <?php foreach ($recent_loans as $loan): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-3 sm:px-4 py-3">
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($loan['nama_lengkap']); ?></div>
                                            <div class="text-gray-500 text-xs"><?= htmlspecialchars($loan['nim']); ?></div>
                                        </td>
                                        <td class="px-3 sm:px-4 py-3 text-gray-700">
                                            <div class="line-clamp-2"><?= htmlspecialchars($loan['judul_buku']); ?></div>
                                        </td>
                                        <td class="px-3 sm:px-4 py-3">
                                            <?php
                                            $statusConfig = [
                                                'dipinjam' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Dipinjam'],
                                                'dikembalikan' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Kembali'],
                                                'terlambat' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Terlambat']
                                            ];
                                            $status = $statusConfig[$loan['status']];
                                            ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium <?= $status['bg']; ?> <?= $status['text']; ?>">
                                                <?= $status['label']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-gray-400 text-sm">
                                        Tidak ada data peminjaman
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-amber-600"></i>
                        <span class="hidden sm:inline">Stok Menipis</span>
                        <span class="sm:hidden">Stok</span>
                    </h3>
                    <a href="buku.php" class="text-xs sm:text-sm text-blue-600 hover:text-blue-700 font-medium">
                        Lihat Semua →
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead class="bg-gray-50 text-gray-700 text-xs">
                            <tr>
                                <th class="px-3 sm:px-4 py-3 text-left font-medium">Kode</th>
                                <th class="px-3 sm:px-4 py-3 text-left font-medium">Judul</th>
                                <th class="px-3 sm:px-4 py-3 text-left font-medium">Stok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (count($low_stock) > 0): ?>
                                <?php foreach ($low_stock as $book): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-3 sm:px-4 py-3 font-mono text-gray-700 text-xs">
                                            <?= htmlspecialchars($book['kode_buku']); ?>
                                        </td>
                                        <td class="px-3 sm:px-4 py-3 text-gray-900">
                                            <div class="line-clamp-2"><?= htmlspecialchars($book['judul_buku']); ?></div>
                                        </td>
                                        <td class="px-3 sm:px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium <?= ($book['stok'] < 3) ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'; ?>">
                                                <?= $book['stok']; ?> unit
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-gray-400 text-sm">
                                        Semua stok aman ✓
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>