<?php
session_start();
include '../../config/database.php';

// =======================================================
// BAGIAN 1: PROSES UPDATE SETELAH FORM DISUBMIT
// =======================================================
if (isset($_POST['submit'])) {

    // Validasi hak akses
    if ($_SESSION['level'] != 'Admin') {
        die("Akses ditolak.");
    }

    // Ambil data dari form
    $kode_pengguna = $_POST['kode_admin'];
    $username = htmlspecialchars($_POST['username']);
    $password_baru = $_POST['password_baru'];

    // [PERBAIKAN] Logika IF dihapus. Sekarang admin bisa mengubah password siapa saja.
    if (!empty($password_baru)) {
        // Jika field password baru diisi, maka hash dan update username + password
        $hash_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        
        $sql = "UPDATE tbl_user SET username=?, password=? WHERE kode_pengguna=?";
        $stmt = mysqli_prepare($kon, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $username, $hash_password_baru, $kode_pengguna);
    } else {
        // Jika field password baru dikosongkan, maka HANYA update username
        $sql = "UPDATE tbl_user SET username=? WHERE kode_pengguna=?";
        $stmt = mysqli_prepare($kon, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $kode_pengguna);
    }

    // Eksekusi query dan redirect
    if (mysqli_stmt_execute($stmt)) {
        header("Location:../../index.php?page=admin&pengguna=berhasil");
    } else {
        header("Location:../../index.php?page=admin&pengguna=gagal");
    }
    exit();
}


// =======================================================
// BAGIAN 2: MENGAMBIL DATA UNTUK DITAMPILKAN DI FORM
// =======================================================
$kode_pengguna = $_POST['kode_admin'];
$stmt_select = mysqli_prepare($kon, "SELECT username FROM tbl_user WHERE kode_pengguna = ?");
mysqli_stmt_bind_param($stmt_select, "s", $kode_pengguna);
mysqli_stmt_execute($stmt_select);
$hasil = mysqli_stmt_get_result($stmt_select);
$data = mysqli_fetch_array($hasil);
?>

<!-- ======================================================= -->
<!-- BAGIAN 3: TAMPILAN FORM -->
<!-- ======================================================= -->
<form action="apps/admin/pengguna.php" method="post">
    <input type="hidden" name="kode_admin" value="<?php echo htmlspecialchars($kode_pengguna); ?>">
    
    <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($data['username']); ?>" required>
    </div>

    <!-- [PERBAIKAN] Blok PHP if-else dihapus. Field password sekarang selalu ditampilkan. -->
    <div class="mb-3">
        <label class="form-label">Password Baru</label>
        <input type="password" name="password_baru" class="form-control" placeholder="Isi untuk mengubah password">
        <div class="form-text">Biarkan kosong jika tidak ingin mengubah password yang sudah ada.</div>
    </div>

    <hr>
    <div class="d-flex justify-content-end">
        <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<!-- Anda bisa tetap menyertakan script AJAX untuk cek username di sini jika masih digunakan -->