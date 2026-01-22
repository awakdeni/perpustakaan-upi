<?php
$page_title = 'Profil';
require_once '../includes/header_admin.php';
require_once '../config/database.php';

$conn = getConnection();
$admin = getAdminData();
$message = '';
$message_type = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        $password_hash = md5($password);
        $stmt = $conn->prepare("UPDATE admin SET nama_lengkap=?, email=?, password=? WHERE id_admin=?");
        $stmt->bind_param("sssi", $nama_lengkap, $email, $password_hash, $admin['id']);
    } else {
        $stmt = $conn->prepare("UPDATE admin SET nama_lengkap=?, email=? WHERE id_admin=?");
        $stmt->bind_param("ssi", $nama_lengkap, $email, $admin['id']);
    }

    if ($stmt->execute()) {
        $_SESSION['admin_nama'] = $nama_lengkap;
        $_SESSION['admin_email'] = $email;
        $message = 'Profil berhasil diupdate!';
        $message_type = 'success';
        $admin = getAdminData();
    } else {
        $message = 'Gagal mengupdate profil: ' . $stmt->error;
        $message_type = 'error';
    }
    $stmt->close();
}

// Get admin data
$admin_data = $conn->query("SELECT * FROM admin WHERE id_admin = " . $admin['id'])->fetch_assoc();
closeConnection($conn);
?>
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        
        <!-- Page Header -->
        <div class="mb-6">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Profil Saya</h2>
            <p class="text-sm sm:text-base text-gray-600">Kelola informasi profil Anda</p>
        </div>

        <!-- Message Alert -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg border max-w-2xl <?php echo $message_type == 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'; ?>">
                <div class="flex items-center gap-2">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span class="text-sm sm:text-base"><?php echo htmlspecialchars($message); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Profile Card -->
        <div class="max-w-2xl">
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <!-- Card Header -->
                <div class="bg-white border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-circle text-amber-600 text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Informasi Profil</h3>
                            <p class="text-xs text-gray-500">Update data profil Anda</p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form method="POST" class="p-6">
                    <div class="space-y-5">
                        <!-- Username (Disabled) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Username
                            </label>
                            <input type="text" value="<?php echo htmlspecialchars($admin_data['username']); ?>" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed text-sm" 
                                   disabled>
                            <p class="text-xs text-gray-500 mt-1.5">Username tidak dapat diubah</p>
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($admin_data['nama_lengkap']); ?>" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none text-sm" 
                                   required>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($admin_data['email']); ?>" 
                                   placeholder="admin@example.com"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none text-sm">
                        </div>

                        <!-- Password Baru -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Password Baru
                            </label>
                            <input type="password" name="password" 
                                   placeholder="Kosongkan jika tidak ingin mengubah"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none text-sm">
                            <p class="text-xs text-gray-500 mt-1.5">Kosongkan jika tidak ingin mengubah password</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-6 pt-6 border-t border-gray-200 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                        <a href="dashboard.php" class="w-full sm:w-auto text-center px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition text-sm">
                            Batal
                        </a>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition text-sm flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
