<?php
$page_title = 'Peminjaman Saya';
require_once '../includes/header_student.php';
require_once '../config/database.php';

$conn = getConnection();
$student = getStudentData();

/* =======================
   FILTER STATUS
======================= */
$status_filter = $_GET['status'] ?? '';
$where = "WHERE p.id_user = {$student['id']}";
if ($status_filter) {
    $where .= " AND p.status = '$status_filter'";
}

$loans = $conn->query("
    SELECT p.*, b.judul_buku, b.kode_buku, b.pengarang, k.nama_kategori,
           DATEDIFF(CURDATE(), p.tanggal_kembali) as hari_terlambat
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id_buku
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
    $where
    ORDER BY p.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>

<div class="min-h-screen bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

<!-- HEADER -->
<div class="mb-6">
    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Peminjaman Saya</h2>
    <p class="text-sm sm:text-base text-gray-600">Riwayat peminjaman buku Anda</p>
</div>

<!-- FILTER -->
<div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
<form method="GET" class="flex flex-col sm:flex-row gap-2">
    <select name="status" class="px-4 py-2.5 border border-gray-300 rounded-lg">
        <option value="">Semua Status</option>
        <option value="dipinjam" <?= $status_filter == 'dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
        <option value="dikembalikan" <?= $status_filter == 'dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
        <option value="terlambat" <?= $status_filter == 'terlambat' ? 'selected' : ''; ?>>Terlambat</option>
    </select>

    <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm">
        <i class="fas fa-filter mr-1"></i> Filter
    </button>

    <?php if ($status_filter): ?>
    <a href="peminjaman_saya.php"
       class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2.5 rounded-lg text-sm text-center">
        Reset
    </a>
    <?php endif; ?>
</form>
</div>

<!-- TABLE -->
<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-gray-50 border-b border-gray-200">
<tr>
    <th class="px-4 py-3 text-left font-semibold text-gray-700">Buku</th>
    <th class="px-4 py-3 hidden md:table-cell font-semibold text-gray-700">Kategori</th>
    <th class="px-4 py-3 hidden md:table-cell font-semibold text-gray-700">Tgl Pinjam</th>
    <th class="px-4 py-3 hidden md:table-cell font-semibold text-gray-700">Tgl Kembali</th>
    <th class="px-4 py-3 font-semibold text-gray-700">Status</th>
    <th class="px-4 py-3 hidden lg:table-cell font-semibold text-gray-700">Denda</th>
</tr>
</thead>

<tbody class="divide-y divide-gray-100">
<?php if ($loans): foreach ($loans as $l): ?>
<tr class="hover:bg-gray-50 transition">

<td class="px-4 py-3">
    <div class="font-medium text-gray-900"><?= htmlspecialchars($l['judul_buku']); ?></div>
    <div class="text-xs text-gray-500"><?= htmlspecialchars($l['kode_buku']); ?></div>
    <div class="text-xs text-gray-500 md:hidden mt-1">
        <?= date('d/m/Y', strtotime($l['tanggal_pinjam'])); ?> -
        <?= date('d/m/Y', strtotime($l['tanggal_kembali'])); ?>
    </div>
</td>

<td class="px-4 py-3 hidden md:table-cell">
    <?= htmlspecialchars($l['nama_kategori'] ?? 'Umum'); ?>
</td>

<td class="px-4 py-3 hidden md:table-cell">
    <?= date('d/m/Y', strtotime($l['tanggal_pinjam'])); ?>
</td>

<td class="px-4 py-3 hidden md:table-cell">
    <?= date('d/m/Y', strtotime($l['tanggal_kembali'])); ?>
</td>

<td class="px-4 py-3">
<?php
$status_cfg = [
    'dipinjam' => ['bg'=>'bg-blue-100','text'=>'text-blue-700','label'=>'Dipinjam'],
    'dikembalikan' => ['bg'=>'bg-green-100','text'=>'text-green-700','label'=>'Dikembalikan'],
    'terlambat' => ['bg'=>'bg-red-100','text'=>'text-red-700','label'=>'Terlambat'],
];
$s = $status_cfg[$l['status']];
?>
<span class="inline-flex px-2 py-1 rounded-md text-xs font-semibold <?= $s['bg'].' '.$s['text']; ?>">
    <?= $s['label']; ?>
</span>

<?php if ($l['status'] == 'dipinjam' && $l['hari_terlambat'] > 0): ?>
<div class="text-red-600 text-xs mt-1">
    Terlambat <?= $l['hari_terlambat']; ?> hari
</div>
<?php endif; ?>
</td>

<td class="px-4 py-3 hidden lg:table-cell">
<?php if ($l['denda'] > 0): ?>
<span class="font-semibold text-red-600">
Rp <?= number_format($l['denda'],0,',','.'); ?>
</span>
<?php else: ?>
<span class="text-gray-400">-</span>
<?php endif; ?>
</td>

</tr>
<?php endforeach; else: ?>
<tr>
<td colspan="6" class="px-4 py-12 text-center">
    <div class="flex flex-col items-center text-gray-400">
        <i class="fas fa-book-open text-5xl mb-3"></i>
        <p class="text-sm">Belum ada riwayat peminjaman</p>
        <a href="buku.php"
           class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm">
            <i class="fas fa-book mr-1"></i> Lihat Katalog
        </a>
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

<?php require_once '../includes/footer.php'; ?>
