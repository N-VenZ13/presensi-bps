<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'Admin') { die("Akses ditolak."); }
include '../../config/database.php';
?>
<form action="apps/cetak/cetak_kegiatan_a.php" method="post" target="_blank">
    <div class="mb-3">
        <label class="form-label">Pilih Peserta Magang</label>
        <select class="form-select" name="id_mahasiswa" required>
            <option value="" selected disabled>-- Pilih salah satu --</option>
            <?php
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
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="tanggal_awal" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="tanggal_akhir" class="form-control" required>
        </div>
    </div>
    <hr>
    <div class="d-flex justify-content-end">
        <button type="submit" name="cetak" class="btn btn-primary">
            <i class="bi bi-printer-fill me-1"></i> Cetak
        </button>
    </div>
</form>