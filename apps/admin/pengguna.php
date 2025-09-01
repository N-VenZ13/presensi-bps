<?php
session_start();
include '../../config/database.php';

// =======================================================
// BAGIAN PROSES UPDATE
// =======================================================
if (isset($_POST['submit'])) {

    if ($_SESSION['level'] != 'Admin') die("Akses ditolak.");

    $kode_pengguna = $_POST['kode_admin'];
    $username = htmlspecialchars($_POST['username']);
    $password_baru = $_POST['password_baru'];

    // Cek apakah admin mencoba mengubah password dirinya sendiri
    $bisa_ubah_password = ($_SESSION['kode_pengguna'] == $kode_pengguna);

    if (!empty($password_baru) && $bisa_ubah_password) {
        // [PERBAIKAN] Gunakan password_hash() bukan md5()
        $hash_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        $sql = "UPDATE tbl_user SET username=?, password=? WHERE kode_pengguna=?";
        $stmt = mysqli_prepare($kon, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $username, $hash_password_baru, $kode_pengguna);
    } else {
        // Jika password dikosongkan atau mencoba mengubah password orang lain, hanya update username
        $sql = "UPDATE tbl_user SET username=? WHERE kode_pengguna=?";
        $stmt = mysqli_prepare($kon, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $kode_pengguna);
    }

    if (mysqli_stmt_execute($stmt)) {
        header("Location:../../index.php?page=admin&pengguna=berhasil");
    } else {
        header("Location:../../index.php?page=admin&pengguna=gagal");
    }
    exit();
}

// =======================================================
// BAGIAN TAMPILAN FORM
// =======================================================
$kode_pengguna = $_POST['kode_admin'];
// [PERBAIKAN KEAMANAN] Gunakan prepared statement
$stmt_select = mysqli_prepare($kon, "SELECT username FROM tbl_user WHERE kode_pengguna = ?");
mysqli_stmt_bind_param($stmt_select, "s", $kode_pengguna);
mysqli_stmt_execute($stmt_select);
$hasil = mysqli_stmt_get_result($stmt_select);
$data = mysqli_fetch_array($hasil);
?>

<form action="apps/admin/pengguna.php" method="post">
    <input type="hidden" name="kode_admin" value="<?php echo htmlspecialchars($kode_pengguna); ?>">
    
    <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($data['username']); ?>" required>
    </div>

    <?php if ($_SESSION['kode_pengguna'] == $kode_pengguna): // Hanya tampilkan field password jika admin mengedit dirinya sendiri ?>
        <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <input type="password" name="password_baru" class="form-control" placeholder="Isi untuk mengubah password Anda">
            <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Anda tidak dapat mengubah password administrator lain.</div>
    <?php endif; ?>

    <hr>
    <div class="d-flex justify-content-end">
        <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>