<?php
// ==========================================================
// [DARI INDEX.PHP LAMA] - BAGIAN 1: SESSION & SECURITY CHECK
// ==========================================================
session_start();
// Jika tidak ada session, redirect ke halaman login
if (!isset($_SESSION["kode_pengguna"])) {
    header("Location: login.php");
    exit(); // Selalu exit setelah redirect
}

// Menghubungkan ke database
include 'config/database.php';

// Verifikasi ulang session untuk keamanan tambahan
$kode_pengguna = $_SESSION["kode_pengguna"];
$username_session = $_SESSION["username"];

$stmt = mysqli_prepare($kon, "SELECT username FROM tbl_user WHERE kode_pengguna = ?");
mysqli_stmt_bind_param($stmt, "s", $kode_pengguna);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_array($result);
$username_db = $data['username'] ?? null;

// Jika username di session tidak cocok dengan di DB, hancurkan session
if ($username_session != $username_db) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
// ==========================================================
// AKHIR DARI BAGIAN 1
// ==========================================================


// ==========================================================
// [DARI INDEX.PHP LAMA] - BAGIAN 2: MENGAMBIL INFO SITUS
// ==========================================================
$query_site = mysqli_query($kon, "SELECT * FROM tbl_site LIMIT 1");
$site_info = mysqli_fetch_array($query_site);
$nama_instansi = $site_info['nama_instansi'];
$logo = $site_info['logo'];
// ==========================================================
// AKHIR DARI BAGIAN 2
// ==========================================================

// Variabel untuk menandai halaman aktif di sidebar
$page = $_GET['page'] ?? 'beranda'; // Default ke beranda jika tidak ada
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- [DIUBAH] Title dan Favicon dinamis dari DB -->
    <title><?php echo htmlspecialchars(ucfirst($page)) . " | " . htmlspecialchars($nama_instansi); ?></title>
    <link rel="shortcut icon" href="apps/pengaturan/logo/<?php echo htmlspecialchars($logo); ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Style kustom kita -->
    <link href="/absensi-magang/template/redesign/css/custom_style.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- =============================================== -->
        <!-- BAGIAN SIDEBAR (MENU KIRI) - DINAMIS -->
        <!-- =============================================== -->
        <div class="sidebar d-flex flex-column p-3">
            <div class="sidebar-header">
                <!-- [DARI INDEX.PHP LAMA] Menampilkan info user sesuai level -->
                <?php if ($_SESSION['level'] == 'Admin'): ?>
                    <img src="source/img/profile.png" class="sidebar-avatar" alt="Avatar Admin">
                    <h5 class="user-name mt-2 mb-0"><?php echo htmlspecialchars($_SESSION['nama_admin']); ?></h5>
                    <small class="user-level">Administrator</small>
                <?php else: // Mahasiswa 
                ?>
                    <img src="apps/mahasiswa/foto/<?php echo htmlspecialchars($_SESSION['foto']); ?>" class="sidebar-avatar" alt="Foto Mahasiswa">
                    <h5 class="user-name mt-2 mb-0"><?php echo htmlspecialchars($_SESSION['nama_mahasiswa']); ?></h5>
                    <small class="user-level">Mahasiswa</small>
                <?php endif; ?>
            </div>

            <ul class="nav flex-column mb-auto">
                <!-- [DARI INDEX.PHP LAMA] Menu dibuat dinamis -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($page == 'beranda') ? 'active' : ''; ?>" href="index.php?page=beranda">
                        <i class="bi bi-house-door-fill"></i> Beranda
                    </a>
                </li>

                <!-- Menu Admin -->
                <?php if ($_SESSION["level"] == "Admin"): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 'mahasiswa') ? 'active' : ''; ?>" href="index.php?page=mahasiswa">
                            <i class="bi bi-people-fill"></i> Data Mahasiswa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 'data_absensi') ? 'active' : ''; ?>" href="index.php?page=data_absensi">
                            <i class="bi bi-calendar-check-fill"></i> Data Absensi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 'data_kegiatan') ? 'active' : ''; ?>" href="index.php?page=data_kegiatan">
                            <i class="bi bi-file-earmark-text-fill"></i> Data Kegiatan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 'admin') ? 'active' : ''; ?>" href="index.php?page=admin">
                            <i class="bi bi-person-badge-fill"></i> Administrator
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 'pengaturan') ? 'active' : ''; ?>" href="index.php?page=pengaturan">
                            <i class="bi bi-gear-fill"></i> Pengaturan
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Menu Mahasiswa -->
                <?php if ($_SESSION["level"] == "Mahasiswa"): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 'absen') ? 'active' : ''; ?>" href="index.php?page=absen">
                            <i class="bi bi-calendar-plus-fill"></i> Absensi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 'riwayat') ? 'active' : ''; ?>" href="index.php?page=riwayat">
                            <i class="bi bi-clock-history"></i> Riwayat Absensi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 'kegiatan') ? 'active' : ''; ?>" href="index.php?page=kegiatan">
                            <i class="bi bi-journal-text"></i> Kegiatan Harian
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page == 'profil') ? 'active' : ''; ?>" href="index.php?page=profil">
                            <i class="bi bi-person-circle"></i> Profil
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <hr class="text-secondary">
            <div class="nav-item">
                <a class="nav-link" href="logout.php" id="tombol-keluar">
                    <i class="bi bi-box-arrow-left"></i> Keluar
                </a>
            </div>
        </div>

        <!-- =============================================== -->
        <!-- BAGIAN KONTEN UTAMA (KANAN) -->
        <!-- =============================================== -->
        <div class="main-content">
            <nav class="navbar-top d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <!-- [BARU] Tombol Hamburger untuk Toggle Sidebar -->
                    <button class="btn btn-link text-dark fs-4 me-2" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="fs-5 fw-bold text-uppercase"><?php echo str_replace('_', ' ', $page); ?></span>
                </div>
                <span class="text-muted d-none d-md-block">Selamat Datang di Sistem Absensi Magang!</span>
            </nav>

            <div class="content-area">
                <!-- ========================================================== -->
                <!-- [DARI INDEX.PHP LAMA] - BAGIAN 3: PAGE ROUTER / PENGHUBUNG -->
                <!-- ========================================================== -->
                <?php
                switch ($page) {
                    case 'beranda':
                        include "apps/beranda/index.php";
                        break;
                    case 'admin':
                        include "apps/admin/index.php";
                        break;
                    case 'mahasiswa':
                        include "apps/mahasiswa/index.php";
                        break;
                    case 'data_absensi':
                        include "apps/data_absensi/index.php";
                        break;
                    case 'data_kegiatan':
                        include "apps/data_kegiatan/index.php";
                        break;
                    case 'pengaturan':
                        include "apps/pengaturan/index.php";
                        break;
                    case 'absen':
                        include "apps/pengguna/absen.php";
                        break;
                    case 'riwayat':
                        include "apps/data_absensi/riwayat.php";
                        break;
                    case 'kegiatan':
                        include "apps/data_kegiatan/kegiatan.php";
                        break;
                    case 'profil':
                        include "apps/pengguna/profil.php";
                        break;
                    default:
                        echo "<div class='alert alert-danger'>Maaf. Halaman tidak ditemukan!</div>";
                        break;
                }
                ?>
                <!-- ========================================================== -->
                <!-- AKHIR DARI BAGIAN 3 -->
                <!-- ========================================================== -->
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- [DARI INDEX.PHP LAMA] Script konfirmasi keluar -->
    <script>
        document.getElementById('tombol-keluar').addEventListener('click', function(e) {
            if (!confirm("Apakah Anda yakin ingin keluar?")) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>