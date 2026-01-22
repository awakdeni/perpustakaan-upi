<?php
require_once '../config/session.php';
requireAdminLogin();
$admin = getAdminData();

function navItem($file, $icon, $label) {
    $active = basename($_SERVER['PHP_SELF']) == $file;
    $base   = "flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-semibold transition-all";
    $cls    = $active
        ? "bg-amber-500 text-white shadow-md"
        : "text-gray-600 hover:bg-amber-50 hover:text-amber-600";

    echo "<a href='$file' class='$base $cls'>
            <i class='$icon w-4'></i>
            <span>$label</span>
          </a>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? $page_title.' - ' : '' ?>Admin Panel</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50 min-h-screen">

<!-- HEADER -->
<header class="bg-white/80 backdrop-blur border-b sticky top-0 z-50">
<div class="max-w-7xl mx-auto px-4">
<div class="flex h-16 items-center justify-between">

<!-- Logo -->
<div class="flex items-center gap-3">
    <div class="w-10 h-10 bg-amber-500 text-white rounded-xl flex items-center justify-center shadow">
        <i class="fas fa-book-reader"></i>
    </div>
    <div>
        <h1 class="font-bold text-gray-800 leading-tight">Perpustakaan</h1>
        <p class="text-xs text-gray-500">UPI YPTK</p>
    </div>
</div>

<!-- Desktop Menu -->
<nav class="hidden lg:flex items-center gap-1">
<?php
navItem('dashboard.php','fas fa-home','Dashboard');
navItem('buku.php','fas fa-book','Buku');
navItem('kategori.php','fas fa-tags','Kategori');
navItem('peminjaman.php','fas fa-exchange-alt','Peminjaman');
navItem('users.php','fas fa-users','Mahasiswa');
?>
</nav>

<!-- User -->
<div class="hidden lg:flex items-center gap-4 relative">
    <div class="text-right">
        <p class="text-sm font-semibold text-gray-700">
            <?= htmlspecialchars($admin['nama_lengkap']) ?>
        </p>
        <p class="text-xs text-gray-500">
            <?= htmlspecialchars($admin['username']) ?>
        </p>
    </div>

    <button id="userBtn"
        class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-white font-bold shadow focus:ring-2 focus:ring-amber-400">
        <?= strtoupper(substr($admin['nama_lengkap'],0,1)) ?>
    </button>

    <!-- Dropdown -->
    <div id="userMenu"
        class="hidden absolute right-0 top-12 w-48 bg-white rounded-xl shadow-lg border overflow-hidden">
        <a href="profile.php" class="block px-4 py-2 text-sm hover:bg-gray-100">
            <i class="fas fa-user mr-2"></i> Profil
        </a>
        <a href="settings.php" class="block px-4 py-2 text-sm hover:bg-gray-100">
            <i class="fas fa-cog mr-2"></i> Pengaturan
        </a>
        <div class="border-t"></div>
        <a href="../logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
    </div>
</div>

<!-- Mobile Button -->
<button id="mobileBtn" class="lg:hidden text-gray-700">
    <i class="fas fa-bars text-xl"></i>
</button>

</div>
</div>

<!-- Mobile Menu -->
<div id="mobileMenu" class="hidden lg:hidden bg-white border-t shadow-lg">
<div class="p-4 space-y-1">
<?php
navItem('dashboard.php','fas fa-home','Dashboard');
navItem('buku.php','fas fa-book','Buku');
navItem('kategori.php','fas fa-tags','Kategori');
navItem('peminjaman.php','fas fa-exchange-alt','Peminjaman');
navItem('users.php','fas fa-users','Mahasiswa');
?>
<div class="border-t my-2"></div>
<a href="../logout.php" class="block px-4 py-2 text-red-600 font-semibold">
    <i class="fas fa-sign-out-alt mr-2"></i> Logout
</a>
</div>
</div>
</header>

<script>
const mobileBtn = document.getElementById('mobileBtn');
const mobileMenu = document.getElementById('mobileMenu');
const userBtn = document.getElementById('userBtn');
const userMenu = document.getElementById('userMenu');

mobileBtn.onclick = () => mobileMenu.classList.toggle('hidden');

userBtn.onclick = () => userMenu.classList.toggle('hidden');

document.addEventListener('click', e => {
    if(!userBtn.contains(e.target) && !userMenu.contains(e.target)) {
        userMenu.classList.add('hidden');
    }
});
</script>
