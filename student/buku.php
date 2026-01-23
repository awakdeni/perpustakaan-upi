<?php
$page_title = 'Katalog Buku';
require_once '../includes/header_student.php';
require_once '../config/database.php';

$conn = getConnection();
$student = getStudentData();

/* =======================
   HANDLE PINJAM
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'borrow') {

    $id_buku = intval($_POST['id_buku']);
    $tanggal_pinjam = date('Y-m-d');
    $tanggal_kembali = $_POST['tanggal_kembali'] ?? '';

    if (!$tanggal_kembali || $tanggal_kembali < $tanggal_pinjam) {
        $message = 'Tanggal pengembalian tidak valid!';
        $message_type = 'error';
    } else {

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
                    $message = 'Buku berhasil dipinjam sampai ' . date('d M Y', strtotime($tanggal_kembali));
                    $message_type = 'success';
                } else {
                    $message = 'Gagal meminjam buku!';
                    $message_type = 'error';
                }
                $stmt->close();
            }
        }
    }
}

/* =======================
   FILTER
======================= */
$search   = $_GET['search'] ?? '';
$kategori = intval($_GET['kategori'] ?? 0);

$where = "WHERE 1";
$params = [];
$types = "";

if ($search) {
    $where .= " AND (b.judul_buku LIKE ? OR b.pengarang LIKE ? OR b.kode_buku LIKE ?)";
    $params = array_fill(0, 3, "%$search%");
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

<div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 py-8">
<div class="max-w-7xl mx-auto px-4">

<!-- HEADER -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-900">Katalog Buku</h2>
    <p class="text-gray-600 mt-1">Pilih buku favorit dan tentukan tanggal pengembaliannya</p>
</div>

<!-- ALERT -->
<?php if (isset($message)): ?>
<div class="mb-6 rounded-xl border px-5 py-4 flex items-center gap-3
<?= $message_type === 'success'
    ? 'bg-green-50 border-green-200 text-green-800'
    : 'bg-red-50 border-red-200 text-red-800'; ?>">
    <i class="fas <?= $message_type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> text-lg"></i>
    <span class="text-sm font-medium"><?= htmlspecialchars($message); ?></span>
</div>
<?php endif; ?>

<!-- FILTER -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
<form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
    <input type="text" name="search"
        value="<?= htmlspecialchars($search); ?>"
        placeholder="Cari judul / pengarang / kode buku..."
        class="sm:col-span-2 px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">

    <select name="kategori"
        class="px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <option value="0">Semua Kategori</option>
        <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id_kategori']; ?>" <?= $kategori == $c['id_kategori'] ? 'selected' : ''; ?>>
            <?= htmlspecialchars($c['nama_kategori']); ?>
        </option>
        <?php endforeach; ?>
    </select>

    <div class="flex gap-2">
        <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
            <i class="fas fa-search mr-1"></i> Cari
        </button>
        <?php if ($search || $kategori): ?>
        <a href="buku.php"
           class="flex-1 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-center py-2.5 font-medium transition">
            Reset
        </a>
        <?php endif; ?>
    </div>
</form>
</div>

<!-- TABLE -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-gray-50 border-b">
<tr>
    <th class="w-24 px-4 py-3 text-left font-semibold text-gray-700">Cover</th>
    <th class="px-4 py-3 text-left font-semibold text-gray-700">Judul Buku</th>
    <th class="px-4 py-3 text-left font-semibold text-gray-700">Kategori</th>
    <th class="px-4 py-3 text-left font-semibold text-gray-700">Pengarang</th>
    <th class="w-24 px-4 py-3 text-center font-semibold text-gray-700">Stok</th>
    <th class="w-52 px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
</tr>
</thead>

<tbody class="divide-y">
<?php if ($books): foreach ($books as $b): ?>
<tr class="hover:bg-gray-50 transition">
    <!-- COVER -->
    <td class="px-4 py-4">
        <div class="flex justify-center">
        <?php if ($b['foto_buku'] && file_exists('../uploads/buku/' . $b['foto_buku'])): ?>
            <img src="../uploads/buku/<?= htmlspecialchars($b['foto_buku']); ?>"
                 class="w-14 h-20 object-cover rounded-lg shadow-sm">
        <?php else: ?>
            <div class="w-14 h-20 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-book text-blue-500 text-xl"></i>
            </div>
        <?php endif; ?>
        </div>
    </td>

    <!-- JUDUL -->
    <td class="px-4 py-4">
        <div class="font-semibold text-gray-900 leading-tight">
            <?= htmlspecialchars($b['judul_buku']); ?>
        </div>
        <div class="text-xs text-gray-500 mt-1">
            <?= htmlspecialchars($b['kode_buku']); ?>
        </div>
    </td>

    <!-- KATEGORI -->
    <td class="px-4 py-4 ">
        <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-700 text-xs">
            <?= htmlspecialchars($b['nama_kategori'] ?? 'Umum'); ?>
        </span>
    </td>

    <!-- PENGARANG -->
    <td class="px-4 py-4 ">
        <?= htmlspecialchars($b['pengarang']); ?>
    </td>

    <!-- STOK -->
    <td class="px-4 py-4 ">
        <span class="px-3 py-1 rounded-full text-xs font-bold
            <?= $b['stok'] < 5 ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700'; ?>">
            <?= $b['stok']; ?>
        </span>
    </td>

    <!-- AKSI -->
    <td class="px-4 py-4">
        <form method="POST" class="flex flex-col gap-2">
            <input type="hidden" name="action" value="borrow">
            <input type="hidden" name="id_buku" value="<?= $b['id_buku']; ?>">

            <input type="date" name="tanggal_kembali" required
                   min="<?= date('Y-m-d'); ?>"
                   value="<?= date('Y-m-d', strtotime('+7 days')); ?>"
                   class="border rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-green-500 focus:outline-none">

            <button
                class="bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-xs font-semibold transition">
                <i class="fas fa-hand-holding mr-1"></i> Pinjam Buku
            </button>
        </form>
    </td>
</tr>
<?php endforeach; else: ?>
<tr>
    <td colspan="6" class="py-16 text-center">
        <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 text-sm">Tidak ada buku yang ditemukan</p>
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
