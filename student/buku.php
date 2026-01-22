<?php
$page_title = 'Katalog Buku';
require_once '../includes/header_student.php';
require_once '../config/database.php';

$conn = getConnection();
$student = getStudentData();

/* =======================
   HANDLE PINJAM BUKU
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'borrow') {
    $id_buku = intval($_POST['id_buku']);
    $tanggal_pinjam = date('Y-m-d');
    $tanggal_kembali = date('Y-m-d', strtotime('+7 days'));

    $stok = $conn->query("SELECT stok FROM buku WHERE id_buku = $id_buku")->fetch_assoc();

    if ($stok['stok'] < 1) {
        $message = 'Stok buku tidak mencukupi!';
        $message_type = 'error';
    } else {
        $cek = $conn->query("
            SELECT 1 FROM peminjaman 
            WHERE id_user = {$student['id']} 
            AND id_buku = $id_buku 
            AND status = 'dipinjam'
        ");

        if ($cek->num_rows > 0) {
            $message = 'Anda sudah meminjam buku ini!';
            $message_type = 'error';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO peminjaman (id_user, id_buku, tanggal_pinjam, tanggal_kembali)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iiss", $student['id'], $id_buku, $tanggal_pinjam, $tanggal_kembali);

            if ($stmt->execute()) {
                $conn->query("UPDATE buku SET stok = stok - 1 WHERE id_buku = $id_buku");
                $message = 'Buku berhasil dipinjam!';
                $message_type = 'success';
            } else {
                $message = 'Gagal meminjam buku!';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

/* =======================
   FILTER
======================= */
$search = $_GET['search'] ?? '';
$kategori = intval($_GET['kategori'] ?? 0);

$where = "WHERE 1";
$params = [];
$types = "";

if ($search) {
    $where .= " AND (b.judul_buku LIKE ? OR b.pengarang LIKE ? OR b.kode_buku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "sss";
}

if ($kategori > 0) {
    $where .= " AND b.id_kategori = ?";
    $params[] = $kategori;
    $types .= "i";
}

$sql = "
    SELECT b.*, k.nama_kategori
    FROM buku b
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
    $where
    ORDER BY b.judul_buku
";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetch_all(MYSQLI_ASSOC);
closeConnection($conn);
?>

<div class="min-h-screen bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

<!-- HEADER -->
<div class="mb-6">
    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Katalog Buku</h2>
    <p class="text-sm sm:text-base text-gray-600">Pilih dan pinjam buku yang tersedia</p>
</div>

<!-- ALERT -->
<?php if (isset($message)): ?>
<div class="mb-6 p-4 rounded-lg border 
    <?= $message_type === 'success'
        ? 'bg-green-50 border-green-200 text-green-800'
        : 'bg-red-50 border-red-200 text-red-800'; ?>">
    <i class="fas <?= $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
    <?= htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<!-- FILTER -->
<div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
<form method="GET" class="flex flex-col sm:flex-row gap-2">
    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>"
        placeholder="Cari judul / pengarang / kode..."
        class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-200">

    <select name="kategori" class="px-4 py-2.5 border border-gray-300 rounded-lg">
        <option value="0">Semua Kategori</option>
        <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id_kategori']; ?>" <?= $kategori == $c['id_kategori'] ? 'selected' : ''; ?>>
            <?= htmlspecialchars($c['nama_kategori']); ?>
        </option>
        <?php endforeach; ?>
    </select>

    <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg">
        <i class="fas fa-search mr-1"></i> Cari
    </button>

    <?php if ($search || $kategori): ?>
    <a href="buku.php" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2.5 rounded-lg">
        Reset
    </a>
    <?php endif; ?>
</form>
</div>

<!-- TABLE -->
<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-gray-50 border-b">
<tr>
    <th class="px-4 py-3 text-left">Foto</th>
    <th class="px-4 py-3 text-left">Buku</th>
    <th class="px-4 py-3 hidden md:table-cell">Kategori</th>
    <th class="px-4 py-3 hidden md:table-cell">Pengarang</th>
    <th class="px-4 py-3">Stok</th>
    <th class="px-4 py-3 text-center">Aksi</th>
</tr>
</thead>

<tbody class="divide-y">
<?php if ($books): foreach ($books as $b): ?>
<tr class="hover:bg-gray-50">
<td class="px-4 py-3">
    <?php if ($b['foto_buku'] && file_exists('../uploads/buku/' . $b['foto_buku'])): ?>
        <img src="../uploads/buku/<?= htmlspecialchars($b['foto_buku']); ?>" 
             alt="<?= htmlspecialchars($b['judul_buku']); ?>" 
             class="w-16 h-20 object-cover rounded-lg shadow-sm">
    <?php else: ?>
        <div class="w-16 h-20 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center">
            <i class="fas fa-book text-blue-500 text-2xl"></i>
        </div>
    <?php endif; ?>
</td>
<td class="px-4 py-3">
    <div class="font-medium"><?= htmlspecialchars($b['judul_buku']); ?></div>
    <div class="text-xs text-gray-500"><?= htmlspecialchars($b['kode_buku']); ?></div>
</td>

<td class="px-4 py-3 hidden md:table-cell">
    <?= htmlspecialchars($b['nama_kategori'] ?? 'Umum'); ?>
</td>

<td class="px-4 py-3 hidden md:table-cell">
    <?= htmlspecialchars($b['pengarang']); ?>
</td>

<td class="px-4 py-3">
<span class="px-2 py-1 rounded text-xs font-semibold
<?= $b['stok'] < 5 ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700'; ?>">
<?= $b['stok']; ?>
</span>
</td>

<td class="px-4 py-3 text-center">
<form method="POST">
<input type="hidden" name="action" value="borrow">
<input type="hidden" name="id_buku" value="<?= $b['id_buku']; ?>">
<button class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg text-xs">
<i class="fas fa-hand-holding mr-1"></i> Pinjam
</button>
</form>
</td>
</tr>
<?php endforeach; else: ?>
<tr>
<td colspan="6" class="py-10 text-center text-gray-400">
<i class="fas fa-book-open text-4xl mb-2"></i>
<p>Tidak ada buku ditemukan</p>
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
