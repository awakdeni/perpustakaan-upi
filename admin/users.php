<?php
$page_title = 'Manajemen Mahasiswa';
require_once '../includes/header_admin.php';
require_once '../config/database.php';

$conn = getConnection();
$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $nim = trim($_POST['nim']);
            $password = md5(trim($_POST['password']));
            $nama_lengkap = trim($_POST['nama_lengkap']);
            $jurusan = trim($_POST['jurusan']);
            $email = trim($_POST['email']);
            $no_hp = trim($_POST['no_hp']);

            $stmt = $conn->prepare("INSERT INTO users (nim, password, nama_lengkap, jurusan, email, no_hp) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nim, $password, $nama_lengkap, $jurusan, $email, $no_hp);
            
            if ($stmt->execute()) {
                $message = 'Mahasiswa berhasil ditambahkan!';
                $message_type = 'success';
            } else {
                $message = 'Gagal menambahkan mahasiswa: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'edit') {
            $id_user = intval($_POST['id_user']);
            $nim = trim($_POST['nim']);
            $nama_lengkap = trim($_POST['nama_lengkap']);
            $jurusan = trim($_POST['jurusan']);
            $email = trim($_POST['email']);
            $no_hp = trim($_POST['no_hp']);
            
            if (!empty($_POST['password'])) {
                $password = md5(trim($_POST['password']));
                $stmt = $conn->prepare("UPDATE users SET nim=?, password=?, nama_lengkap=?, jurusan=?, email=?, no_hp=? WHERE id_user=?");
                $stmt->bind_param("ssssssi", $nim, $password, $nama_lengkap, $jurusan, $email, $no_hp, $id_user);
            } else {
                $stmt = $conn->prepare("UPDATE users SET nim=?, nama_lengkap=?, jurusan=?, email=?, no_hp=? WHERE id_user=?");
                $stmt->bind_param("sssssi", $nim, $nama_lengkap, $jurusan, $email, $no_hp, $id_user);
            }
            
            if ($stmt->execute()) {
                $message = 'Mahasiswa berhasil diupdate!';
                $message_type = 'success';
            } else {
                $message = 'Gagal mengupdate mahasiswa: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'delete') {
            $id_user = intval($_POST['id_user']);
            $stmt = $conn->prepare("DELETE FROM users WHERE id_user=?");
            $stmt->bind_param("i", $id_user);
            
            if ($stmt->execute()) {
                $message = 'Mahasiswa berhasil dihapus!';
                $message_type = 'success';
            } else {
                $message = 'Gagal menghapus mahasiswa: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

// Get users
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = "";
if ($search) {
    $search_term = "%$search%";
    $where = "WHERE u.nim LIKE ? OR u.nama_lengkap LIKE ? OR u.jurusan LIKE ?";
}

$query = "SELECT u.*, COUNT(p.id_peminjaman) as total_peminjaman 
          FROM users u 
          LEFT JOIN peminjaman p ON u.id_user = p.id_user 
          $where
          GROUP BY u.id_user
          ORDER BY u.created_at DESC";

if ($search) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $users = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

// Get user for edit
$edit_user = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_user = $result->fetch_assoc();
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
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Manajemen Mahasiswa</h2>
                <p class="text-sm sm:text-base text-gray-600">Kelola data mahasiswa</p>
            </div>
            <button onclick="toggleModal()" class="w-full sm:w-auto bg-amber-600 hover:bg-amber-700 text-white font-medium py-2.5 px-5 rounded-lg transition duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Tambah Mahasiswa</span>
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

        <!-- Search -->
        <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
            <form method="GET" class="flex flex-col sm:flex-row gap-2">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Cari mahasiswa (NIM, nama, jurusan)..." 
                       class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none text-sm">
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 sm:flex-none bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    <?php if ($search): ?>
                        <a href="users.php" class="flex-1 sm:flex-none bg-gray-500 hover:bg-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition text-center">
                            <i class="fas fa-times mr-2"></i>Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">NIM</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 hidden md:table-cell">Jurusan</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 hidden lg:table-cell">Email</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 hidden lg:table-cell">No. HP</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Peminjaman</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-mono font-semibold text-gray-900"><?php echo htmlspecialchars($user['nim']); ?></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($user['nama_lengkap']); ?></div>
                                        <div class="text-xs text-gray-500 mt-1 md:hidden">
                                            <?php echo htmlspecialchars($user['jurusan'] ?: '-'); ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 hidden md:table-cell">
                                        <?php echo htmlspecialchars($user['jurusan'] ?: '-'); ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 hidden lg:table-cell">
                                        <?php echo htmlspecialchars($user['email'] ?: '-'); ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 hidden lg:table-cell">
                                        <?php echo htmlspecialchars($user['no_hp'] ?: '-'); ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-blue-100 text-blue-700">
                                            <?php echo $user['total_peminjaman']; ?> buku
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center items-center gap-2">
                                            <a href="?edit=<?php echo $user['id_user']; ?>" 
                                               class="text-blue-600 hover:text-blue-800 transition" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_user" value="<?php echo $user['id_user']; ?>">
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-800 transition" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-users text-5xl mb-3"></i>
                                        <p class="text-sm">Tidak ada data mahasiswa</p>
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

<!-- Modal Add/Edit User -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center rounded-t-xl">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-<?php echo $edit_user ? 'edit' : 'plus'; ?> text-amber-600 text-sm"></i>
                </div>
                <span><?php echo $edit_user ? 'Edit Mahasiswa' : 'Tambah Mahasiswa'; ?></span>
            </h3>
            <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Form -->
        <form method="POST" class="p-6">
            <input type="hidden" name="action" value="<?php echo $edit_user ? 'edit' : 'add'; ?>">
            <?php if ($edit_user): ?>
                <input type="hidden" name="id_user" value="<?php echo $edit_user['id_user']; ?>">
            <?php endif; ?>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- NIM -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        NIM <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nim" value="<?php echo $edit_user ? htmlspecialchars($edit_user['nim']) : ''; ?>" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none text-sm" required>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password <?php echo $edit_user ? '(kosongkan jika tidak diubah)' : '<span class="text-red-500">*</span>'; ?>
                    </label>
                    <input type="password" name="password" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none text-sm" 
                           <?php echo $edit_user ? '' : 'required'; ?>>
                </div>

                <!-- Nama Lengkap -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_lengkap" value="<?php echo $edit_user ? htmlspecialchars($edit_user['nama_lengkap']) : ''; ?>" 
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none text-sm" required>
                </div>

                <!-- Jurusan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jurusan</label>
                    <input type="text" name="jurusan" value="<?php echo $edit_user ? htmlspecialchars($edit_user['jurusan']) : ''; ?>" 
                           placeholder="Contoh: Teknik Informatika"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none text-sm">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" 
                           placeholder="contoh@email.com"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none text-sm">
                </div>

                <!-- No. HP -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">No. HP</label>
                    <input type="text" name="no_hp" value="<?php echo $edit_user ? htmlspecialchars($edit_user['no_hp']) : ''; ?>" 
                           placeholder="08xx-xxxx-xxxx"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none text-sm">
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                <button type="button" onclick="toggleModal()" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition text-sm">
                    Batal
                </button>
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal() {
    const modal = document.getElementById('userModal');
    modal.classList.toggle('hidden');
    modal.classList.toggle('flex');
}

<?php if ($edit_user): ?>
window.onload = function() {
    toggleModal();
};
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>