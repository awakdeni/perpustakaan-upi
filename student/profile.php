<?php
$page_title = 'Profil';
require_once '../includes/header_student.php';
require_once '../config/database.php';

$conn = getConnection();
$student = getStudentData();
$message = '';
$message_type = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $jurusan = trim($_POST['jurusan']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        $password_hash = md5($password);
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, jurusan=?, email=?, no_hp=?, password=? WHERE id_user=?");
        $stmt->bind_param("sssssi", $nama_lengkap, $jurusan, $email, $no_hp, $password_hash, $student['id']);
    } else {
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, jurusan=?, email=?, no_hp=? WHERE id_user=?");
        $stmt->bind_param("ssssi", $nama_lengkap, $jurusan, $email, $no_hp, $student['id']);
    }

    if ($stmt->execute()) {
        $_SESSION['user_nama'] = $nama_lengkap;
        $_SESSION['user_jurusan'] = $jurusan;
        $_SESSION['user_email'] = $email;
        $message = 'Profil berhasil diupdate!';
        $message_type = 'success';
        $student = getStudentData();
    } else {
        $message = 'Gagal mengupdate profil: ' . $stmt->error;
        $message_type = 'error';
    }
    $stmt->close();
}

// Get student data
$student_data = $conn->query("SELECT * FROM users WHERE id_user = " . $student['id'])->fetch_assoc();
closeConnection($conn);
?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">
            <i class="fas fa-user-circle text-yellow-600 mr-2"></i>Profil Saya
        </h2>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type == 'success' ? 'bg-green-100 text-green-800 border-l-4 border-green-500' : 'bg-red-100 text-red-800 border-l-4 border-red-500'; ?>">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="POST">
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">NIM</label>
                    <input type="text" value="<?php echo htmlspecialchars($student_data['nim']); ?>" 
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg bg-gray-100" disabled>
                    <p class="text-gray-500 text-sm mt-1">NIM tidak dapat diubah</p>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($student_data['nama_lengkap']); ?>" 
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-yellow-500 focus:outline-none" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Jurusan</label>
                    <input type="text" name="jurusan" value="<?php echo htmlspecialchars($student_data['jurusan']); ?>" 
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-yellow-500 focus:outline-none">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($student_data['email']); ?>" 
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-yellow-500 focus:outline-none">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">No. HP</label>
                    <input type="text" name="no_hp" value="<?php echo htmlspecialchars($student_data['no_hp']); ?>" 
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-yellow-500 focus:outline-none">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Password Baru</label>
                    <input type="password" name="password" 
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-yellow-500 focus:outline-none"
                           placeholder="Kosongkan jika tidak ingin mengubah password">
                    <p class="text-gray-500 text-sm mt-1">Kosongkan jika tidak ingin mengubah password</p>
                </div>
                <div class="flex justify-end space-x-3">
                    <a href="dashboard.php" class="px-6 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg font-semibold">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-semibold">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
