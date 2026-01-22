<?php
$page_title = 'Manajemen Peminjaman';
require_once '../includes/header_admin.php';
require_once '../config/database.php';

$conn = getConnection();
$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $id_user = intval($_POST['id_user']);
            $id_buku = intval($_POST['id_buku']);
            $tanggal_pinjam = $_POST['tanggal_pinjam'];
            $tanggal_kembali = $_POST['tanggal_kembali'];
            $keterangan = trim($_POST['keterangan']);

            // Check stock
            $stock_check = $conn->query("SELECT stok FROM buku WHERE id_buku = $id_buku")->fetch_assoc();
            if ($stock_check['stok'] < 1) {
                $message = 'Stok buku tidak mencukupi!';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("INSERT INTO peminjaman (id_user, id_buku, tanggal_pinjam, tanggal_kembali, keterangan) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $id_user, $id_buku, $tanggal_pinjam, $tanggal_kembali, $keterangan);
                
                if ($stmt->execute()) {
                    // Decrease stock
                    $conn->query("UPDATE buku SET stok = stok - 1 WHERE id_buku = $id_buku");
                    $message = 'Peminjaman berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan peminjaman: ' . $stmt->error;
                    $message_type = 'error';
                }
                $stmt->close();
            }
        } elseif ($_POST['action'] == 'return') {
            $id_peminjaman = intval($_POST['id_peminjaman']);
            $tanggal_pengembalian = $_POST['tanggal_pengembalian'];
            $denda = floatval($_POST['denda']);
            $status = $_POST['status'];

            // Get book ID
            $loan = $conn->query("SELECT id_buku FROM peminjaman WHERE id_peminjaman = $id_peminjaman")->fetch_assoc();
            
            $stmt = $conn->prepare("UPDATE peminjaman SET tanggal_pengembalian=?, status=?, denda=? WHERE id_peminjaman=?");
            $stmt->bind_param("ssdi", $tanggal_pengembalian, $status, $denda, $id_peminjaman);
            
            if ($stmt->execute()) {
                // Increase stock
                $conn->query("UPDATE buku SET stok = stok + 1 WHERE id_buku = " . $loan['id_buku']);
                $message = 'Buku berhasil dikembalikan!';
                $message_type = 'success';
            } else {
                $message = 'Gagal mengembalikan buku: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

// Get loans
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where = "";
if ($status_filter) {
    $where = "WHERE p.status = '$status_filter'";
}

$loans = $conn->query("
    SELECT p.*, u.nama_lengkap, u.nim, b.judul_buku, b.kode_buku, k.nama_kategori,
           DATEDIFF(CURDATE(), p.tanggal_kembali) as hari_terlambat
    FROM peminjaman p
    JOIN users u ON p.id_user = u.id_user
    JOIN buku b ON p.id_buku = b.id_buku
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
    $where
    ORDER BY p.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Get users and books for dropdown
$users = $conn->query("SELECT * FROM users ORDER BY nama_lengkap")->fetch_all(MYSQLI_ASSOC);
$books = $conn->query("SELECT * FROM buku WHERE stok > 0 ORDER BY judul_buku")->fetch_all(MYSQLI_ASSOC);

// Get loan for return
$return_loan = null;
if (isset($_GET['return'])) {
    $id = intval($_GET['return']);
    $return_loan = $conn->query("
        SELECT p.*, u.nama_lengkap, u.nim, b.judul_buku, b.kode_buku,
               DATEDIFF(CURDATE(), p.tanggal_kembali) as hari_terlambat
        FROM peminjaman p
        JOIN users u ON p.id_user = u.id_user
        JOIN buku b ON p.id_buku = b.id_buku
        WHERE p.id_peminjaman = $id
    ")->fetch_assoc();
}

closeConnection($conn);
?>
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Manajemen Peminjaman</h2>
                <p class="text-sm sm:text-base text-gray-600">Kelola peminjaman dan pengembalian buku</p>
            </div>
            <button onclick="toggleModal()" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-5 rounded-lg transition duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Tambah Peminjaman</span>
            </button>
        </div>

        <!-- Message Alert -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg border <?php echo $message_type == 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'; ?>">
                <div class="flex items-center gap-2">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span class="text-sm sm:text-base"><?php echo htmlspecialchars($message); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filter -->
        <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
            <form method="GET" class="flex flex-col sm:flex-row gap-2">
                <select name="status" class="flex-1 sm:flex-none px-4 py-2.5 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none text-sm">
                    <option value="">Semua Status</option>
                    <option value="dipinjam" <?php echo $status_filter == 'dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
                    <option value="dikembalikan" <?php echo $status_filter == 'dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
                    <option value="terlambat" <?php echo $status_filter == 'terlambat' ? 'selected' : ''; ?>>Terlambat</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <?php if ($status_filter): ?>
                        <a href="peminjaman.php" class="flex-1 sm:flex-none bg-gray-500 hover:bg-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition text-center">
                            <i class="fas fa-times mr-2"></i>Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Loans Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Mahasiswa</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Buku</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 hidden md:table-cell">Tgl Pinjam</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 hidden md:table-cell">Tgl Kembali</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 hidden lg:table-cell">Denda</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($loans) > 0): ?>
                            <?php foreach ($loans as $loan): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($loan['nama_lengkap']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($loan['nim']); ?></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($loan['judul_buku']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($loan['kode_buku']); ?></div>
                                        <div class="text-xs text-gray-500 mt-1 md:hidden">
                                            <?php echo date('d/m/Y', strtotime($loan['tanggal_pinjam'])); ?> - <?php echo date('d/m/Y', strtotime($loan['tanggal_kembali'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 hidden md:table-cell"><?php echo date('d/m/Y', strtotime($loan['tanggal_pinjam'])); ?></td>
                                    <td class="px-4 py-3 text-gray-700 hidden md:table-cell"><?php echo date('d/m/Y', strtotime($loan['tanggal_kembali'])); ?></td>
                                    <td class="px-4 py-3">
                                        <?php
                                        $status_config = [
                                            'dipinjam' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Dipinjam'],
                                            'dikembalikan' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Kembali'],
                                            'terlambat' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Terlambat']
                                        ];
                                        $status = $status_config[$loan['status']];
                                        ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold <?php echo $status['bg']; ?> <?php echo $status['text']; ?>">
                                            <?php echo $status['label']; ?>
                                        </span>
                                        <?php if ($loan['status'] == 'dipinjam' && $loan['hari_terlambat'] > 0): ?>
                                            <div class="text-red-600 text-xs mt-1"><?php echo $loan['hari_terlambat']; ?> hari</div>
                                        <?php endif; ?>
                                        <?php if ($loan['denda'] > 0): ?>
                                            <div class="text-gray-600 text-xs mt-1 lg:hidden">Rp <?php echo number_format($loan['denda'], 0, ',', '.'); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 hidden lg:table-cell">
                                        <span class="font-semibold text-gray-900">Rp <?php echo number_format($loan['denda'], 0, ',', '.'); ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center items-center">
                                            <?php if ($loan['status'] == 'dipinjam'): ?>
                                                <a href="?return=<?php echo $loan['id_peminjaman']; ?>" 
                                                   class="inline-flex items-center gap-1 bg-green-50 hover:bg-green-100 text-green-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                                    <i class="fas fa-undo"></i>
                                                    <span class="hidden sm:inline">Kembalikan</span>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-exchange-alt text-5xl mb-3"></i>
                                        <p class="text-sm">Tidak ada data peminjaman</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Loan -->
<div id="loanModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center rounded-t-xl">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-plus text-blue-600 text-sm"></i>
                </div>
                <span>Tambah Peminjaman</span>
            </h3>
            <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Form -->
        <form method="POST" class="p-6">
            <input type="hidden" name="action" value="add">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Mahasiswa -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Mahasiswa <span class="text-red-500">*</span>
                    </label>
                    <select name="id_user" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none text-sm" required>
                        <option value="">Pilih Mahasiswa</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id_user']; ?>">
                                <?php echo htmlspecialchars($user['nim'] . ' - ' . $user['nama_lengkap']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buku -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Buku <span class="text-red-500">*</span>
                    </label>
                    <select name="id_buku" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none text-sm" required>
                        <option value="">Pilih Buku</option>
                        <?php foreach ($books as $book): ?>
                            <option value="<?php echo $book['id_buku']; ?>">
                                <?php echo htmlspecialchars($book['kode_buku'] . ' - ' . $book['judul_buku'] . ' (Stok: ' . $book['stok'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tanggal Pinjam -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Pinjam <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_pinjam" value="<?php echo date('Y-m-d'); ?>" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none text-sm" required>
                </div>

                <!-- Tanggal Kembali -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Kembali <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_kembali" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none text-sm" required>
                </div>

                <!-- Keterangan -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" rows="3" 
                              placeholder="Catatan tambahan (opsional)"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none text-sm resize-none"></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                <button type="button" onclick="toggleModal()" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition text-sm">
                    Batal
                </button>
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Return Book -->
<?php if ($return_loan): ?>
<div id="returnModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
        <!-- Modal Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center rounded-t-xl">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-undo text-green-600 text-sm"></i>
                </div>
                <span>Pengembalian Buku</span>
            </h3>
            <a href="peminjaman.php" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-xl"></i>
            </a>
        </div>

        <!-- Modal Form -->
        <form method="POST" class="p-6">
            <input type="hidden" name="action" value="return">
            <input type="hidden" name="id_peminjaman" value="<?php echo $return_loan['id_peminjaman']; ?>">
            
            <!-- Info Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4 space-y-3">
                <div>
                    <p class="text-xs text-gray-600 mb-1">Mahasiswa</p>
                    <p class="font-semibold text-sm text-gray-900"><?php echo htmlspecialchars($return_loan['nama_lengkap']); ?></p>
                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($return_loan['nim']); ?></p>
                </div>
                <div class="border-t border-gray-200 pt-3">
                    <p class="text-xs text-gray-600 mb-1">Buku</p>
                    <p class="font-semibold text-sm text-gray-900"><?php echo htmlspecialchars($return_loan['judul_buku']); ?></p>
                </div>
                <div class="border-t border-gray-200 pt-3">
                    <p class="text-xs text-gray-600 mb-1">Tanggal Kembali Seharusnya</p>
                    <p class="font-semibold text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($return_loan['tanggal_kembali'])); ?></p>
                    <?php if ($return_loan['hari_terlambat'] > 0): ?>
                        <div class="mt-2 inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 rounded-md text-xs font-medium">
                            <i class="fas fa-exclamation-triangle"></i>
                            Terlambat <?php echo $return_loan['hari_terlambat']; ?> hari
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tanggal Pengembalian -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Pengembalian <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal_pengembalian" value="<?php echo date('Y-m-d'); ?>" 
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 focus:outline-none text-sm" required>
            </div>

            <!-- Status -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Status <span class="text-red-500">*</span>
                </label>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 focus:outline-none text-sm" required>
                    <option value="dikembalikan">Dikembalikan</option>
                    <option value="terlambat" <?php echo $return_loan['hari_terlambat'] > 0 ? 'selected' : ''; ?>>Terlambat</option>
                </select>
            </div>

            <!-- Denda -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Denda (Rp)</label>
                <input type="number" name="denda" value="<?php echo $return_loan['hari_terlambat'] > 0 ? $return_loan['hari_terlambat'] * 5000 : 0; ?>" 
                       min="0" step="1000" 
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 focus:outline-none text-sm">
                <p class="text-xs text-gray-500 mt-1">Denda Rp 5.000 per hari keterlambatan</p>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                <a href="peminjaman.php" class="w-full sm:w-auto text-center px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition text-sm">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-check"></i>
                    <span>Konfirmasi Pengembalian</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function toggleModal() {
    const modal = document.getElementById('loanModal');
    modal.classList.toggle('hidden');
    modal.classList.toggle('flex');
}
</script>

<?php require_once '../includes/footer.php'; ?>
