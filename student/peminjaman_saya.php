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

<div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
    <div>
        <h2 class="text-3xl font-bold text-gray-900">Peminjaman Saya</h2>
        <p class="text-sm text-gray-600 mt-1">Riwayat peminjaman buku yang pernah Anda lakukan</p>
    </div>
</div>

<!-- FILTER -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
<form method="GET" class="flex flex-col sm:flex-row gap-3">
    <select name="status"
        class="w-full sm:w-56 px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <option value="">Semua Status</option>
        <option value="dipinjam" <?= $status_filter == 'dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
        <option value="dikembalikan" <?= $status_filter == 'dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
        <option value="terlambat" <?= $status_filter == 'terlambat' ? 'selected' : ''; ?>>Terlambat</option>
    </select>

    <button
        class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
        <i class="fas fa-filter"></i> Filter
    </button>

    <?php if ($status_filter): ?>
    <a href="peminjaman_saya.php"
       class="inline-flex items-center justify-center gap-2 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
        <i class="fas fa-rotate-left"></i> Reset
    </a>
    <?php endif; ?>
</form>
</div>

<!-- TABLE -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
<div class="overflow-x-auto">
<table class="min-w-full text-sm">
<thead class="bg-gray-50 border-b">
<tr>
    <th class="px-5 py-3 text-left  font-semibold text-gray-700">Buku</th>
    <th class="px-5 py-3 text-left  font-semibold text-gray-700">Kategori</th>
    <th class="px-5 py-3 text-left  font-semibold text-gray-700">Pinjam</th>
    <th class="px-5 py-3 text-left  font-semibold text-gray-700">Kembali</th>
    <th class="px-5 py-3 text-left  font-semibold text-gray-700">Status</th>
    <th class="px-5 py-3 text-left  font-semibold text-gray-700">Denda</th>
</tr>
</thead>

<tbody class="divide-y">
<?php if ($loans): foreach ($loans as $l): ?>
<tr class="hover:bg-gray-50 transition">
<td class="px-5 py-4">
    <div class="font-semibold text-gray-900">
        <?= htmlspecialchars($l['judul_buku']); ?>
    </div>
    <div class="text-xs text-gray-500">
        <?= htmlspecialchars($l['kode_buku']); ?>
    </div>


</td>

<td class="px-5 py-4 ">
    <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-700 text-xs">
        <?= htmlspecialchars($l['nama_kategori'] ?? 'Umum'); ?>
    </span>
</td>

<td class="px-5 py-4 ">
    <?= date('d/m/Y', strtotime($l['tanggal_pinjam'])); ?>
</td>

<td class="px-5 py-4 ">
    <?= date('d/m/Y', strtotime($l['tanggal_kembali'])); ?>
</td>

<td class="px-5 py-4">
<?php
$status_cfg = [
    'dipinjam' => ['bg'=>'bg-blue-100','text'=>'text-blue-700','label'=>'Dipinjam'],
    'dikembalikan' => ['bg'=>'bg-green-100','text'=>'text-green-700','label'=>'Dikembalikan'],
    'terlambat' => ['bg'=>'bg-red-100','text'=>'text-red-700','label'=>'Terlambat'],
];
$s = $status_cfg[$l['status']];
?>
<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?= $s['bg'].' '.$s['text']; ?>">
    <?= $s['label']; ?>
</span>

<?php if ($l['status'] == 'dipinjam' && $l['hari_terlambat'] > 0): ?>
<div class="mt-1 text-xs text-red-600 font-medium">
    ⚠ Terlambat <?= $l['hari_terlambat']; ?> hari
</div>
<?php endif; ?>
</td>

<td class="px-5 py-4 hidden">
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
<td colspan="6" class="px-6 py-16 text-center">
    <div class="flex flex-col items-center">
        <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 text-sm mb-4">Belum ada riwayat peminjaman buku</p>
        <a href="buku.php"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
            <i class="fas fa-book"></i> Lihat Katalog
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
