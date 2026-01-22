<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: admin/dashboard.php');
    exit();
}
if (isStudentLoggedIn()) {
    header('Location: student/dashboard.php');
    exit();
}

$error = '';
$type = isset($_GET['type']) ? $_GET['type'] : 'student';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_type = $_POST['login_type'];
    $username_nim = trim($_POST['username_nim']);
    $password = $_POST['password'];

    if (empty($username_nim) || empty($password)) {
        $error = 'Username/NIM dan Password harus diisi!';
    } else {
        $conn = getConnection();

        if ($login_type == 'admin') {
            // Admin login
            $stmt = $conn->prepare("SELECT id_admin, username, password, nama_lengkap, email FROM admin WHERE username = ?");
            $stmt->bind_param("s", $username_nim);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $admin = $result->fetch_assoc();
                if (md5($password) == $admin['password']) {
                    $_SESSION['admin_id'] = $admin['id_admin'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_nama'] = $admin['nama_lengkap'];
                    $_SESSION['admin_email'] = $admin['email'];
                    header('Location: admin/dashboard.php');
                    exit();
                } else {
                    $error = 'Password salah!';
                }
            } else {
                $error = 'Username tidak ditemukan!';
            }
            $stmt->close();
        } else {
            // Student login
            $stmt = $conn->prepare("SELECT id_user, nim, password, nama_lengkap, jurusan, email FROM users WHERE nim = ?");
            $stmt->bind_param("s", $username_nim);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (md5($password) == $user['password']) {
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_nim'] = $user['nim'];
                    $_SESSION['user_nama'] = $user['nama_lengkap'];
                    $_SESSION['user_jurusan'] = $user['jurusan'];
                    $_SESSION['user_email'] = $user['email'];
                    header('Location: student/dashboard.php');
                    exit();
                } else {
                    $error = 'Password salah!';
                }
            } else {
                $error = 'NIM tidak ditemukan!';
            }
            $stmt->close();
        }

        closeConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan UPI YPTK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-amber-50 via-white to-yellow-50 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Decorative Background Elements -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-amber-200 rounded-full filter blur-3xl opacity-20 animate-float"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-yellow-200 rounded-full filter blur-3xl opacity-20 animate-float" style="animation-delay: 1.5s;"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-amber-300 rounded-full filter blur-3xl opacity-10 animate-float" style="animation-delay: 3s;"></div>

    <div class="w-full max-w-md relative z-10">

        <!-- Logo and Title -->
        <div class="text-center mb-8 animate-fade-in">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-amber-400 to-yellow-500 rounded-3xl mb-4 shadow-2xl animate-float">
                <i class="fas fa-book-reader text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-amber-600 to-yellow-600 bg-clip-text text-transparent mb-2">
                Perpustakaan UPI YPTK
            </h1>
            <p class="text-gray-600 text-sm md:text-base">Sistem Informasi Perpustakaan Digital</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-6 md:p-8 border border-gray-100 animate-slide-up">
            <!-- Tab Selection -->
            <div class="flex mb-8 bg-gray-100 rounded-2xl p-1.5">
                <button onclick="switchTab('student')" id="tab-student" class="flex-1 py-3 px-4 rounded-xl font-semibold transition-all duration-300 flex items-center justify-center space-x-2 <?php echo $type == 'student' ? 'bg-gradient-to-r from-amber-500 to-yellow-500 text-white shadow-lg' : 'text-gray-600 hover:text-gray-800'; ?>">
                    <i class="fas fa-user-graduate"></i>
                    <span class="hidden sm:inline">Mahasiswa</span>
                    <span class="sm:hidden">Mahasiswa</span>
                </button>
                <button onclick="switchTab('admin')" id="tab-admin" class="flex-1 py-3 px-4 rounded-xl font-semibold transition-all duration-300 flex items-center justify-center space-x-2 <?php echo $type == 'admin' ? 'bg-gradient-to-r from-amber-500 to-yellow-500 text-white shadow-lg' : 'text-gray-600 hover:text-gray-800'; ?>">
                    <i class="fas fa-user-shield"></i>
                    <span class="hidden sm:inline">Admin</span>
                    <span class="sm:hidden">Admin</span>
                </button>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg animate-slide-up">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="" id="loginForm" class="space-y-6">
                <input type="hidden" name="login_type" id="login_type" value="<?php echo $type; ?>">
                
                <!-- Username/NIM Field -->
                <div class="space-y-2">
                    <label for="username_nim" class="block text-gray-700 font-semibold text-sm">
                        <i class="fas fa-<?php echo $type == 'admin' ? 'user' : 'id-card'; ?> mr-2 text-amber-600"></i>
                        <span id="label-text"><?php echo $type == 'admin' ? 'Username' : 'NIM'; ?></span>
                    </label>
                    <div class="relative">
                        <input type="text" id="username_nim" name="username_nim" 
                               class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:border-amber-500 focus:ring-4 focus:ring-amber-100 focus:outline-none transition-all duration-300" 
                               placeholder="<?php echo $type == 'admin' ? 'Masukkan username' : 'Masukkan NIM'; ?>" 
                               required autofocus>
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-<?php echo $type == 'admin' ? 'user' : 'id-card'; ?>" id="input-icon"></i>
                        </div>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="space-y-2">
                    <label for="password" class="block text-gray-700 font-semibold text-sm">
                        <i class="fas fa-lock mr-2 text-amber-600"></i>
                        Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" 
                               class="w-full pl-12 pr-12 py-3.5 border-2 border-gray-200 rounded-xl focus:border-amber-500 focus:ring-4 focus:ring-amber-100 focus:outline-none transition-all duration-300" 
                               placeholder="Masukkan password" required>
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </div>
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-amber-600 transition-colors duration-300 focus:outline-none">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center space-x-2 cursor-pointer group">
                        <input type="checkbox" class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500 focus:ring-2">
                        <span class="text-gray-600 group-hover:text-gray-800">Ingat saya</span>
                    </label>
                    <a href="#" class="text-amber-600 hover:text-amber-700 font-medium transition-colors duration-300">
                        Lupa password?
                    </a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white font-bold py-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-amber-200">
                    <span class="flex items-center justify-center space-x-2">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Masuk</span>
                    </span>
                </button>
            </form>

            <!-- Demo Info -->
            <div class="mt-6 p-4 bg-gradient-to-r from-amber-50 to-yellow-50 rounded-xl border border-amber-100">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-info-circle text-amber-600 mt-0.5"></i>
                    <div class="text-sm text-gray-700">
                        <p class="font-semibold mb-1">Akun Demo:</p>
                        <p class="text-gray-600">• Admin: <span class="font-mono bg-white px-2 py-0.5 rounded">admin / admin123</span></p>
                        <p class="text-gray-600">• Mahasiswa: <span class="font-mono bg-white px-2 py-0.5 rounded">2024001 / 123456</span></p>
                    </div>
                </div>
            </div>

            <!-- Register Link -->
            <div class="mt-6 text-center">
                <p class="text-gray-600 text-sm">
                    Belum punya akun? 
                    <a href="register.php" class="text-amber-600 hover:text-amber-700 font-semibold transition-colors duration-300">
                        Daftar sekarang
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 animate-fade-in">
            <p class="text-gray-500 text-sm">
                &copy; <?php echo date('Y'); ?> Perpustakaan UPI YPTK. All rights reserved.
            </p>
            <div class="flex items-center justify-center space-x-4 mt-3 text-gray-400 text-xs">
                <a href="#" class="hover:text-amber-600 transition-colors duration-300">Privacy Policy</a>
                <span>•</span>
                <a href="#" class="hover:text-amber-600 transition-colors duration-300">Terms of Service</a>
                <span>•</span>
                <a href="#" class="hover:text-amber-600 transition-colors duration-300">Help</a>
            </div>
        </div>
    </div>

    <script>
        function switchTab(type) {
            document.getElementById('login_type').value = type;
            
            // Update tab styling
            const studentTab = document.getElementById('tab-student');
            const adminTab = document.getElementById('tab-admin');
            
            if (type === 'student') {
                studentTab.className = 'flex-1 py-3 px-4 rounded-xl font-semibold transition-all duration-300 flex items-center justify-center space-x-2 bg-gradient-to-r from-amber-500 to-yellow-500 text-white shadow-lg';
                adminTab.className = 'flex-1 py-3 px-4 rounded-xl font-semibold transition-all duration-300 flex items-center justify-center space-x-2 text-gray-600 hover:text-gray-800';
            } else {
                adminTab.className = 'flex-1 py-3 px-4 rounded-xl font-semibold transition-all duration-300 flex items-center justify-center space-x-2 bg-gradient-to-r from-amber-500 to-yellow-500 text-white shadow-lg';
                studentTab.className = 'flex-1 py-3 px-4 rounded-xl font-semibold transition-all duration-300 flex items-center justify-center space-x-2 text-gray-600 hover:text-gray-800';
            }
            
            // Update label and placeholder
            const label = document.getElementById('label-text');
            const input = document.getElementById('username_nim');
            const icon = document.getElementById('input-icon');
            
            if (type === 'admin') {
                label.innerHTML = 'Username';
                input.placeholder = 'Masukkan username';
                icon.className = 'fas fa-user';
            } else {
                label.innerHTML = 'NIM';
                input.placeholder = 'Masukkan NIM';
                icon.className = 'fas fa-id-card';
            }
            
            input.focus();
        }

        function togglePassword() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Add ripple effect on button click
        document.querySelector('button[type="submit"]').addEventListener('click', function(e) {
            let ripple = document.createElement('span');
            ripple.classList.add('ripple');
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });

        // Auto-hide error message after 5 seconds
        <?php if ($error): ?>
        setTimeout(() => {
            const errorDiv = document.querySelector('.bg-red-50');
            if (errorDiv) {
                errorDiv.style.transition = 'opacity 0.5s ease-out';
                errorDiv.style.opacity = '0';
                setTimeout(() => errorDiv.remove(), 500);
            }
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>