<?php
session_start();
include '../../config/database.php';

// =======================================================
// BAGIAN 1: PROSES UPDATE DATA SETELAH FORM DISUBMIT
// =======================================================
if (isset($_POST['simpan_perubahan'])) {

    if ($_SESSION['level'] != 'Admin') die("Akses ditolak.");

    $id_absensi = (int)$_POST['id_absensi'];
    $status = (int)$_POST['status'];
    $tanggal = $_POST['tanggal'];
    // Ambil waktu masuk & pulang, set ke NULL jika kosong
    $waktu_masuk = !empty($_POST['waktu_masuk']) ? $_POST['waktu_masuk'] : null;
    $waktu_pulang = !empty($_POST['waktu_pulang']) ? $_POST['waktu_pulang'] : null;
    $keterangan = htmlspecialchars($_POST['keterangan']);


    // [PERBAIKAN KEAMANAN] Menggunakan prepared statement untuk UPDATE
    $stmt = mysqli_prepare($kon, "UPDATE tbl_absensi SET status=?, tanggal=?, waktu=?, waktu_pulang=?, keterangan=? WHERE id_absensi=?");
    mysqli_stmt_bind_param($stmt, "issssi", $status, $tanggal, $waktu_masuk, $waktu_pulang, $keterangan, $id_absensi);

    if (mysqli_stmt_execute($stmt)) {
        // Redirect dengan notifikasi sukses
        header("Location:../../index.php?page=data_absensi&edit=berhasil");
    } else {
        // Redirect dengan notifikasi gagal
        header("Location:../../index.php?page=data_absensi&edit=gagal");
    }
    exit();
}

// =======================================================
// BAGIAN 2: MENGAMBIL DATA UNTUK DITAMPILKAN DI FORM
// =======================================================
$id_absensi = (int)$_POST['id_absensi'];
// [PERBAIKAN BUG & KEAMANAN] Query sekarang mengambil SEMUA kolom yang dibutuhkan dengan aman
$stmt_select = mysqli_prepare($kon, "SELECT a.*, m.nama FROM tbl_absensi a JOIN tbl_mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa WHERE a.id_absensi = ?");
mysqli_stmt_bind_param($stmt_select, "i", $id_absensi);
mysqli_stmt_execute($stmt_select);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_select));
?>

<!-- ======================================================= -->
<!-- BAGIAN 3: TAMPILAN FORM YANG MODERN -->
<!-- ======================================================= -->
<form action="apps/data_absensi/absensi.php" method="post">
    <input type="hidden" name="id_absensi" value="<?php echo $data['id_absensi']; ?>">

    <div class="alert alert-light">
        Mengubah data absensi untuk: <strong><?php echo htmlspecialchars($data['nama']); ?></strong>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Tanggal Absensi</label>
            <!-- [PERBAIKAN BUG] Sekarang $data['tanggal'] sudah pasti ada -->
            <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($data['tanggal']); ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Status Absensi</label>
            <select name="status" class="form-select" required>
                <option value="1" <?php if ($data['status'] == 1) echo 'selected'; ?>>Hadir</option>
                <option value="2" <?php if ($data['status'] == 2) echo 'selected'; ?>>Izin Disetujui</option>
                <option value="3" <?php if ($data['status'] == 3) echo 'selected'; ?>>Tidak Hadir</option>
                <option value="4" <?php if ($data['status'] == 4) echo 'selected'; ?>>Izin Ditolak</option>
                <option value="5" <?php if ($data['status'] == 5) echo 'selected'; ?>>Menunggu Persetujuan</option>
            </select>
        </div>

        <div class="col-md-12 mb-3">
            <label class="form-label">Keterangan (Opsional)</label>
            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Terlambat karena ada urusan..."><?php echo htmlspecialchars($data['keterangan']); ?></textarea>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Waktu Masuk</label>
            <input type="time" name="waktu_masuk" class="form-control" value="<?php echo htmlspecialchars($data['waktu']); ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Waktu Pulang</label>
            <input type="time" name="waktu_pulang" class="form-control" value="<?php echo htmlspecialchars($data['waktu_pulang']); ?>">
        </div>
    </div>
    <div class="form-text">Kosongkan waktu masuk/pulang jika statusnya bukan "Hadir".</div>
    <hr>
    <div class="d-flex justify-content-end">
        <button type="submit" name="simpan_perubahan" class="btn btn-primary">
            <i class="bi bi-save"></i> Simpan Perubahan
        </button>
    </div>
</form>