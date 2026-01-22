<?php
$page_title = 'Manajemen Kategori';
require_once '../includes/header_admin.php';
require_once '../config/database.php';

$conn = getConnection();
$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $nama_kategori = trim($_POST['nama_kategori']);
            $keterangan = trim($_POST['keterangan']);

            $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, keterangan) VALUES (?, ?)");
            $stmt->bind_param("ss", $nama_kategori, $keterangan);
            
            if ($stmt->execute()) {
                $message = 'Kategori berhasil ditambahkan!';
                $message_type = 'success';
            } else {
                $message = 'Gagal menambahkan kategori: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'edit') {
            $id_kategori = intval($_POST['id_kategori']);
            $nama_kategori = trim($_POST['nama_kategori']);
            $keterangan = trim($_POST['keterangan']);

            $stmt = $conn->prepare("UPDATE kategori SET nama_kategori=?, keterangan=? WHERE id_kategori=?");
            $stmt->bind_param("ssi", $nama_kategori, $keterangan, $id_kategori);
            
            if ($stmt->execute()) {
                $message = 'Kategori berhasil diupdate!';
                $message_type = 'success';
            } else {
                $message = 'Gagal mengupdate kategori: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'delete') {
            $id_kategori = intval($_POST['id_kategori']);
            $stmt = $conn->prepare("DELETE FROM kategori WHERE id_kategori=?");
            $stmt->bind_param("i", $id_kategori);
            
            if ($stmt->execute()) {
                $message = 'Kategori berhasil dihapus!';
                $message_type = 'success';
            } else {
                $message = 'Gagal menghapus kategori: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

// Get categories
$categories = $conn->query("SELECT k.*, COUNT(b.id_buku) as jumlah_buku 
                            FROM kategori k 
                            LEFT JOIN buku b ON k.id_kategori = b.id_kategori 
                            GROUP BY k.id_kategori 
                            ORDER BY k.nama_kategori")->fetch_all(MYSQLI_ASSOC);

// Get category for edit
$edit_category = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_category = $result->fetch_assoc();
    }
    $stmt->close();
}

closeConnection($conn);
?>
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Manajemen Kategori</h2>
                <p class="text-sm sm:text-base text-gray-600">Kelola kategori buku perpustakaan</p>
            </div>
            <button onclick="toggleModal()" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-5 rounded-lg transition duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Tambah Kategori</span>
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

        <!-- Categories Grid -->
        <?php if (count($categories) > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <?php foreach ($categories as $category): ?>
                    <div class="group bg-white rounded-lg border border-gray-200 p-5 hover:shadow-lg hover:border-gray-300 transition-all duration-300">
                        <!-- Category Header -->
                        <div class="mb-4">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-blue-200 transition-colors">
                                    <i class="fas fa-tag text-blue-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-bold text-gray-900 mb-1 truncate">
                                        <?php echo htmlspecialchars($category['nama_kategori']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600 line-clamp-2">
                                        <?php echo htmlspecialchars($category['keterangan'] ?: 'Tidak ada keterangan'); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Book Count Badge -->
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-50 rounded-lg text-sm">
                                <i class="fas fa-book text-gray-600"></i>
                                <span class="font-medium text-gray-700"><?php echo $category['jumlah_buku']; ?> buku</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 pt-4 border-t border-gray-100">
                            <a href="?edit=<?php echo $category['id_kategori']; ?>" 
                               class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-center py-2 rounded-lg transition duration-200 text-sm font-medium">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <form method="POST" class="flex-1" onsubmit="return confirm('Yakin ingin menghapus kategori ini? Kategori dengan buku tidak dapat dihapus.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_kategori" value="<?php echo $category['id_kategori']; ?>">
                                <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-700 py-2 rounded-lg transition duration-200 text-sm font-medium">
                                    <i class="fas fa-trash mr-1"></i>Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-tags text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada kategori</h3>
                    <p class="text-sm text-gray-600 mb-6">Mulai dengan menambahkan kategori buku pertama Anda</p>
                    <button onclick="toggleModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition">
                        <i class="fas fa-plus mr-2"></i>Tambah Kategori
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Add/Edit Category -->
<div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
        <!-- Modal Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center rounded-t-xl">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-<?php echo $edit_category ? 'edit' : 'plus'; ?> text-blue-600 text-sm"></i>
                </div>
                <span><?php echo $edit_category ? 'Edit Kategori' : 'Tambah Kategori'; ?></span>
            </h3>
            <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Form -->
        <form method="POST" class="p-6">
            <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit' : 'add'; ?>">
            <?php if ($edit_category): ?>
                <input type="hidden" name="id_kategori" value="<?php echo $edit_category['id_kategori']; ?>">
            <?php endif; ?>
            
            <!-- Nama Kategori -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama_kategori" value="<?php echo $edit_category ? htmlspecialchars($edit_category['nama_kategori']) : ''; ?>" 
                       placeholder="Contoh: Fiksi, Non-Fiksi, Teknologi"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none text-sm" required>
            </div>

            <!-- Keterangan -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <textarea name="keterangan" rows="4" 
                          placeholder="Deskripsi singkat tentang kategori ini..."
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none text-sm resize-none"><?php echo $edit_category ? htmlspecialchars($edit_category['keterangan']) : ''; ?></textarea>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
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

<script>
function toggleModal() {
    const modal = document.getElementById('categoryModal');
    modal.classList.toggle('hidden');
    modal.classList.toggle('flex');
}
<?php if ($edit_category): ?>
window.onload = function() {
    toggleModal();
};
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
