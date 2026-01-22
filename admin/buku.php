<?php
// 1. Inisialisasi awal - Wajib di baris paling atas
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memanggil konfigurasi database
require_once '../config/database.php';
$conn = getConnection();

$message = '';
$message_type = '';

// Ambil pesan session (hasil redirect dari proses POST)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// 2. LOGIKA PROSES (POST) - Diletakkan sebelum pemanggilan header HTML
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- PROSES HAPUS ---
    if ($action == 'delete') {
        $id_buku = intval($_POST['id_buku']);
        
        // PERBAIKAN: Gunakan prepared statement untuk query SELECT
        $stmt_select = $conn->prepare("SELECT foto_buku FROM buku WHERE id_buku = ?");
        $stmt_select->bind_param("i", $id_buku);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $book = $result->fetch_assoc();
        $stmt_select->close();
        
        $stmt = $conn->prepare("DELETE FROM buku WHERE id_buku=?");
        $stmt->bind_param("i", $id_buku);
        
        if ($stmt->execute()) {
            // Hapus file foto jika ada
            if ($book && $book['foto_buku'] && file_exists('../uploads/buku/' . $book['foto_buku'])) {
                unlink('../uploads/buku/' . $book['foto_buku']);
            }
            $_SESSION['message'] = 'Buku berhasil dihapus!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal menghapus buku: ' . $stmt->error;
            $_SESSION['message_type'] = 'error';
        }
        $stmt->close();
        header('Location: buku.php');
        exit();
    } 
    
    // --- PROSES TAMBAH & EDIT ---
    else if ($action == 'add' || $action == 'edit') {
        $kode_buku = mysqli_real_escape_string($conn, trim($_POST['kode_buku']));
        $judul_buku = mysqli_real_escape_string($conn, trim($_POST['judul_buku']));
        $pengarang = mysqli_real_escape_string($conn, trim($_POST['pengarang']));
        $penerbit = mysqli_real_escape_string($conn, trim($_POST['penerbit']));
        $tahun_terbit = intval($_POST['tahun_terbit']);
        
        // PERBAIKAN: Handle NULL untuk id_kategori dengan benar
        $id_kategori = !empty($_POST['id_kategori']) ? intval($_POST['id_kategori']) : null;
        
        $stok = intval($_POST['stok']);
        $lokasi_rak = mysqli_real_escape_string($conn, trim($_POST['lokasi_rak']));
        
        $foto_buku = null;
        if ($action == 'edit') {
            $id_buku = intval($_POST['id_buku']);
            $stmt_current = $conn->prepare("SELECT foto_buku FROM buku WHERE id_buku = ?");
            $stmt_current->bind_param("i", $id_buku);
            $stmt_current->execute();
            $result_current = $stmt_current->get_result();
            $current = $result_current->fetch_assoc();
            $foto_buku = $current['foto_buku'];
            $stmt_current->close();
        }

        // PERBAIKAN: Handle Upload Gambar dengan validasi lengkap
        if (isset($_FILES['foto_buku']) && $_FILES['foto_buku']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $file_name = $_FILES['foto_buku']['name'];
            $file_size = $_FILES['foto_buku']['size'];
            $file_tmp = $_FILES['foto_buku']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validasi ekstensi file
            if (!in_array($file_ext, $allowed)) {
                $_SESSION['message'] = 'Format file tidak valid! Hanya jpg, jpeg, png, gif yang diperbolehkan.';
                $_SESSION['message_type'] = 'error';
                header('Location: buku.php');
                exit();
            }
            
            // Validasi ukuran file (maksimal 5MB)
            if ($file_size > 5242880) {
                $_SESSION['message'] = 'Ukuran file terlalu besar! Maksimal 5MB.';
                $_SESSION['message_type'] = 'error';
                header('Location: buku.php');
                exit();
            }
            
            $newname = uniqid() . '_' . time() . '.' . $file_ext;
            $upload_dir = '../uploads/buku/';
            
            // PERBAIKAN: Buat direktori jika belum ada
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    $_SESSION['message'] = 'Gagal membuat direktori upload!';
                    $_SESSION['message_type'] = 'error';
                    header('Location: buku.php');
                    exit();
                }
            }
            
            if (move_uploaded_file($file_tmp, $upload_dir . $newname)) {
                // Hapus foto lama jika ada saat edit
                if ($action == 'edit' && $foto_buku && file_exists($upload_dir . $foto_buku)) {
                    unlink($upload_dir . $foto_buku);
                }
                $foto_buku = $newname;
            } else {
                $_SESSION['message'] = 'Gagal mengupload file!';
                $_SESSION['message_type'] = 'error';
                header('Location: buku.php');
                exit();
            }
        }

        // PERBAIKAN: Query dengan handling NULL untuk id_kategori
        if ($action == 'add') {
            if ($id_kategori === null) {
                $stmt = $conn->prepare("INSERT INTO buku (kode_buku, judul_buku, pengarang, penerbit, tahun_terbit, id_kategori, stok, lokasi_rak, foto_buku) VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?)");
                $stmt->bind_param("ssssiiss", $kode_buku, $judul_buku, $pengarang, $penerbit, $tahun_terbit, $stok, $lokasi_rak, $foto_buku);
            } else {
                $stmt = $conn->prepare("INSERT INTO buku (kode_buku, judul_buku, pengarang, penerbit, tahun_terbit, id_kategori, stok, lokasi_rak, foto_buku) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssiiiss", $kode_buku, $judul_buku, $pengarang, $penerbit, $tahun_terbit, $id_kategori, $stok, $lokasi_rak, $foto_buku);
            }
        } else {
            if ($id_kategori === null) {
                $stmt = $conn->prepare("UPDATE buku SET kode_buku=?, judul_buku=?, pengarang=?, penerbit=?, tahun_terbit=?, id_kategori=NULL, stok=?, lokasi_rak=?, foto_buku=? WHERE id_buku=?");
                $stmt->bind_param("ssssiissi", $kode_buku, $judul_buku, $pengarang, $penerbit, $tahun_terbit, $stok, $lokasi_rak, $foto_buku, $id_buku);
            } else {
                $stmt = $conn->prepare("UPDATE buku SET kode_buku=?, judul_buku=?, pengarang=?, penerbit=?, tahun_terbit=?, id_kategori=?, stok=?, lokasi_rak=?, foto_buku=? WHERE id_buku=?");
                $stmt->bind_param("ssssiiissi", $kode_buku, $judul_buku, $pengarang, $penerbit, $tahun_terbit, $id_kategori, $stok, $lokasi_rak, $foto_buku, $id_buku);
            }
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = ($action == 'add') ? 'Buku berhasil ditambahkan!' : 'Buku berhasil diupdate!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal menyimpan data: ' . $stmt->error;
            $_SESSION['message_type'] = 'error';
        }
        $stmt->close();
        header('Location: buku.php');
        exit();
    }
}

// 3. LOGIKA TAMPILAN (GET)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "";

$query = "SELECT b.*, k.nama_kategori 
          FROM buku b 
          LEFT JOIN kategori k ON b.id_kategori = k.id_kategori";

if ($search) {
    $where = " WHERE b.judul_buku LIKE ? OR b.kode_buku LIKE ? OR b.pengarang LIKE ?";
    $query .= $where;
}

$query .= " ORDER BY b.created_at DESC";

if ($search) {
    $search_term = "%$search%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $books = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $result = $conn->query($query);
    $books = $result->fetch_all(MYSQLI_ASSOC);
}

$categories = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetch_all(MYSQLI_ASSOC);

$edit_book = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM buku WHERE id_buku = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_book = $result->fetch_assoc();
    $stmt->close();
}

// Memanggil header HTML
$page_title = 'Manajemen Buku';
require_once '../includes/header_admin.php';
?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Manajemen Buku</h2>
                <p class="text-gray-600">Kelola koleksi buku perpustakaan</p>
            </div>
            <button onclick="toggleModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-5 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Buku
            </button>
        </div>

        <?php if ($message): ?>
            <div id="alertMessage" class="mb-6 p-4 rounded-lg border <?php echo $message_type == 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'; ?>">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6 shadow-sm">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari buku (judul, kode, pengarang)..." class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 focus:outline-none">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Cari</button>
                <?php if ($search): ?>
                    <a href="buku.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-gray-700">Foto</th>
                            <th class="px-4 py-3 text-gray-700">Kode</th>
                            <th class="px-4 py-3 text-gray-700">Judul Buku</th>
                            <th class="px-4 py-3 text-gray-700">Pengarang</th>
                            <th class="px-4 py-3 text-gray-700">Stok</th>
                            <th class="px-4 py-3 text-center text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($books) > 0): ?>
                            <?php foreach ($books as $book): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">
                                        <?php 
                                        $foto_path = !empty($book['foto_buku']) ? '../uploads/buku/' . htmlspecialchars($book['foto_buku']) : '';
                                        $foto_exists = $foto_path && file_exists($foto_path);
                                        $foto_src = $foto_exists ? $foto_path : 'https://via.placeholder.com/150x200?text=No+Image';
                                        ?>
                                        <img src="<?php echo $foto_src; ?>" 
                                             class="w-12 h-16 object-cover rounded shadow-sm" 
                                             onerror="this.src='https://via.placeholder.com/150x200?text=No+Image'">
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600"><?php echo htmlspecialchars($book['kode_buku']); ?></td>
                                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo htmlspecialchars($book['judul_buku']); ?></td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($book['pengarang']); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-md text-xs font-bold <?php echo $book['stok'] < 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                                            <?php echo $book['stok']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-3">
                                            <a href="?edit=<?php echo $book['id_buku']; ?>" class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                                                <i class="fas fa-edit text-lg"></i>
                                            </a>
                                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?');" class="inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_buku" value="<?php echo $book['id_buku']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-800 transition" title="Hapus">
                                                    <i class="fas fa-trash text-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                    <i class="fas fa-book-open text-4xl mb-2"></i>
                                    <p>Data buku tidak ditemukan</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="bookModal" class="fixed inset-0 bg-black bg-opacity-50 <?php echo $edit_book ? 'flex' : 'hidden'; ?> items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white px-6 py-4 border-b flex justify-between items-center z-10">
            <h3 class="font-bold text-xl text-gray-900">
                <i class="fas <?php echo $edit_book ? 'fa-edit' : 'fa-plus-circle'; ?> text-blue-600 mr-2"></i>
                <?php echo $edit_book ? 'Edit Data Buku' : 'Tambah Buku Baru'; ?>
            </h3>
            <button onclick="window.location.href='buku.php'" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="hidden" name="action" value="<?php echo $edit_book ? 'edit' : 'add'; ?>">
            <?php if ($edit_book): ?>
                <input type="hidden" name="id_buku" value="<?php echo $edit_book['id_buku']; ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Buku *</label>
                <input type="text" name="kode_buku" value="<?php echo $edit_book ? htmlspecialchars($edit_book['kode_buku']) : ''; ?>" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
                <select name="id_kategori" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="">Pilih Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id_kategori']; ?>" <?php echo ($edit_book && $edit_book['id_kategori'] == $cat['id_kategori']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Buku *</label>
                <input type="text" name="judul_buku" value="<?php echo $edit_book ? htmlspecialchars($edit_book['judul_buku']) : ''; ?>" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Pengarang *</label>
                <input type="text" name="pengarang" value="<?php echo $edit_book ? htmlspecialchars($edit_book['pengarang']) : ''; ?>" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Penerbit</label>
                <input type="text" name="penerbit" value="<?php echo $edit_book ? htmlspecialchars($edit_book['penerbit']) : ''; ?>" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Terbit</label>
                <input type="number" name="tahun_terbit" value="<?php echo $edit_book ? $edit_book['tahun_terbit'] : date('Y'); ?>" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Stok *</label>
                <input type="number" name="stok" value="<?php echo $edit_book ? $edit_book['stok'] : '0'; ?>" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none" required>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi Rak</label>
                <input type="text" name="lokasi_rak" value="<?php echo $edit_book ? htmlspecialchars($edit_book['lokasi_rak']) : ''; ?>" placeholder="Contoh: A-01" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Buku (Maks. 5MB)</label>
                <?php if ($edit_book && $edit_book['foto_buku']): ?>
                    <div class="mb-2">
                        <img src="../uploads/buku/<?php echo htmlspecialchars($edit_book['foto_buku']); ?>" class="w-20 h-28 object-cover rounded border" onerror="this.style.display='none'">
                    </div>
                <?php endif; ?>
                <input type="file" name="foto_buku" accept=".jpg,.jpeg,.png,.gif" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG, GIF</p>
            </div>

            <div class="sm:col-span-2 mt-6 pt-4 border-t flex flex-col-reverse sm:flex-row justify-end gap-2">
                <button type="button" onclick="window.location.href='buku.php'" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal() {
    const modal = document.getElementById('bookModal');
    modal.classList.toggle('hidden');
    modal.classList.toggle('flex');
}

// Sembunyikan alert otomatis setelah 5 detik
setTimeout(() => {
    const alert = document.getElementById('alertMessage');
    if (alert) alert.style.display = 'none';
}, 5000);
</script>

<?php 
// Menutup koneksi
closeConnection($conn);
require_once '../includes/footer.php';
ob_end_flush(); 
?>