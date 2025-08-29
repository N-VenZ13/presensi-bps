<?php
// SELALU mulai session di baris paling atas
session_start();

// [LOGIKA LAMA] Jika sudah login, arahkan ke halaman utama, bukan malah destroy session
// Ini lebih logis, jika pengguna sudah login dan mencoba akses halaman login,
// langsung lempar ke dashboard.
if (isset($_SESSION["id_pengguna"])) {
 
    header("Location: index.php?page=beranda");
    exit(); // Selalu exit setelah redirect
}

// Menghubungkan ke file konfigurasi

require_once 'config/database.php';

$pesan_error = "";

// Cek apakah ada kiriman form dari method post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Ambil data dari form (tanpa fungsi input() yang tidak perlu untuk prepared statements)
    $username = $_POST["username"];
    $password = $_POST["password"];

    // 2. Query untuk mencari user berdasarkan username
    // [PERBAIKAN KEAMANAN] Menggunakan Prepared Statement untuk mencegah SQL Injection
    $sql = "SELECT u.id_user, u.kode_pengguna, u.username, u.password, u.level, 
                   a.nama AS nama_admin, a.nip,
                   m.id_mahasiswa, m.nama AS nama_mahasiswa, m.universitas, m.foto, m.nim
            FROM tbl_user u
            LEFT JOIN tbl_admin a ON u.kode_pengguna = a.kode_admin
            LEFT JOIN tbl_mahasiswa m ON u.kode_pengguna = m.kode_mahasiswa
            WHERE u.username = ? 
            LIMIT 1";

    $stmt = mysqli_prepare($kon, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // 3. Verifikasi user dan password
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // [PERBAIKAN KEAMANAN] Verifikasi password dengan password_verify()
        if (password_verify($password, $row['password'])) {
            // Password cocok, buat session
            $_SESSION["id_pengguna"] = $row["id_user"];
            $_SESSION["kode_pengguna"] = $row["kode_pengguna"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["level"] = $row["level"];

            if ($row['level'] == 'Admin') {
                $_SESSION["nama_admin"] = $row["nama_admin"];
                $_SESSION["nip"] = $row["nip"];
            } else if ($row['level'] == 'Mahasiswa') {
                $_SESSION["id_mahasiswa"] = $row["id_mahasiswa"];
                $_SESSION["nama_mahasiswa"] = $row["nama_mahasiswa"];
                $_SESSION["universitas"] = $row["universitas"];
                $_SESSION["foto"] = $row["foto"];
                $_SESSION["nim"] = $row["nim"];
            }
            
            // Arahkan ke halaman utama
            header("Location: index.php?page=beranda");
            exit();

        } else {
            // Password salah
            $pesan_error = "Username atau Password yang Anda masukkan salah.";
        }
    } else {
        // Username tidak ditemukan
        $pesan_error = "Username atau Password yang Anda masukkan salah.";
    }

    mysqli_stmt_close($stmt);
}

// Mengambil profil aplikasi untuk judul halaman
$query_site = mysqli_query($kon, "SELECT * FROM tbl_site LIMIT 1");
$site_info = mysqli_fetch_array($query_site);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- [DIUBAH] Judul dan Favicon dinamis dari DB -->
    <title>Login | <?php echo htmlspecialchars($site_info['nama_instansi']); ?></title>
    <link rel="shortcut icon" href="apps/pengaturan/logo/<?php echo htmlspecialchars($site_info['logo']); ?>">


    <style>
        :root { --primary-color: #F37321; --primary-color-hover: #d8641c; }
        body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .login-card { max-width: 450px; width: 100%; border: none; border-radius: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .login-card .card-body { padding: 2.5rem; }
        .login-logo { max-width: 100px; height: auto; }
        .form-control:focus { box-shadow: none; border-color: var(--primary-color); }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); padding: 0.75rem; font-weight: 600; }
        .btn-primary:hover { background-color: var(--primary-color-hover); border-color: var(--primary-color-hover); }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="card login-card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="/absensi-magang/source/img/logo-bps.png" alt="Logo BPS" class="login-logo">
                    <h4 class="mt-3 mb-1">Sistem Absensi Magang</h4>
                    <p class="text-muted"><?php echo htmlspecialchars($site_info['nama_instansi']); ?></p>
                </div>
                
                <!-- [DIUBAH] Form action dikosongkan agar submit ke halaman ini sendiri -->
                <form action="" method="POST">
                    
                    <!-- [BARU] Menampilkan pesan error jika ada -->
                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $pesan_error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>