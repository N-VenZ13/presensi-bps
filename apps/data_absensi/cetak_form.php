<?php
// Mulai session untuk keamanan (jika diperlukan)
session_start();
// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'Admin') {
    die("Akses ditolak.");
}
include '../../config/database.php';
?>

<!-- Form ini akan membuka laporan cetak di tab baru -->
<form action="apps/cetak/cetak_absensi_a.php" method="post" target="_blank">
    <div class="mb-3">
        <label for="id_mahasiswa_cetak" class="form-label">Pilih Peserta Magang</label>
        <select class="form-select" id="id_mahasiswa_cetak" name="id_mahasiswa" required>
            <option value="" selected disabled>-- Pilih salah satu --</option>
            <?php
            // Ambil daftar semua mahasiswa aktif
            $query = "SELECT id_mahasiswa, nama FROM tbl_mahasiswa WHERE CURDATE() BETWEEN mulai_magang AND akhir_magang ORDER BY nama ASC";
            $result = mysqli_query($kon, $query);
            while ($data = mysqli_fetch_assoc($result)) {
                echo "<option value='" . $data['id_mahasiswa'] . "'>" . htmlspecialchars($data['nama']) . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="tanggal_awal_cetak" class="form-label">Dari Tanggal</label>
            <input type="date" name="tanggal_awal" id="tanggal_awal_cetak" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="tanggal_akhir_cetak" class="form-label">Sampai Tanggal</label>
            <input type="date" name="tanggal_akhir" id="tanggal_akhir_cetak" class="form-control" required>
        </div>
    </div>
    <hr>
    <div class="d-flex justify-content-end">
        <!-- [PERBAIKAN] Tambahkan name="cetak" di sini -->
        <button type="submit" name="cetak" class="btn btn-primary">
            <i class="bi bi-printer-fill me-1"></i> Cetak
        </button>
    </div>
</form>