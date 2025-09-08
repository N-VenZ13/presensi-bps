<?php
date_default_timezone_set('Asia/Jakarta');
if ($_SESSION["level"] != 'Mahasiswa') {
    exit;
}
include 'config/function.php';

$id_mahasiswa = $_SESSION['id_mahasiswa'];
$tanggal_hari_ini = date("Y-m-d");
$hari_ini = strtolower(date("l"));
$hari_libur = ($hari_ini == "saturday" || $hari_ini == "sunday");

$stmt_absen = mysqli_prepare($kon, "SELECT waktu, waktu_pulang, status FROM tbl_absensi WHERE id_mahasiswa = ? AND tanggal = ?");
mysqli_stmt_bind_param($stmt_absen, "is", $id_mahasiswa, $tanggal_hari_ini);
mysqli_stmt_execute($stmt_absen);
$data_absensi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_absen));

$hasil_setting = mysqli_query($kon, "SELECT * FROM tbl_setting_absensi LIMIT 1");
$setting = mysqli_fetch_assoc($hasil_setting);

$waktu_sekarang_obj = new DateTime();
$masuk_mulai_obj = new DateTime($setting['masuk_mulai']);
$masuk_akhir_obj = new DateTime($setting['masuk_akhir']);
$pulang_mulai_obj = new DateTime($setting['pulang_mulai']);
$pulang_akhir_obj = new DateTime($setting['pulang_akhir']);

// [PERBAIKAN] Logika status diperbarui
$status_absensi = "belum_absen";
$izin_ditolak = false; // Penanda baru
if ($data_absensi) {
    $status_db = $data_absensi['status'];

    if ($status_db == 2) {
        $status_absensi = "izin_diterima";
    } elseif ($status_db == 3) {
        $status_absensi = "tidak_hadir";
    } elseif ($status_db == 4) {
        // Jika izin ditolak, anggap dia "belum_absen" tapi beri notifikasi
        $status_absensi = "belum_absen";
        $izin_ditolak = true;
    } elseif ($status_db == 5) {
        $status_absensi = "menunggu_persetujuan";
    } elseif ($status_db == 1) {
        if ($data_absensi['waktu_pulang']) {
            $status_absensi = "sudah_pulang";
        } else {
            $status_absensi = "sudah_masuk";
        }
    }
}

$nama_hari_inggris = date('l');
$nomor_bulan = date('m');
$tanggal_indonesia = MendapatkanHari($nama_hari_inggris) . ", " . date('d') . " " . MendapatkanBulan($nomor_bulan) . " " . date('Y');
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Halaman Absensi</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Formulir Kehadiran - <?php echo $tanggal_indonesia; ?></h5>
    </div>
    <div class="card-body text-center p-4">
        <h1 class="display-4 fw-bold" id="jam-digital"></h1>

        <!-- [BARU] Notifikasi khusus jika izin ditolak -->
        <?php if ($izin_ditolak): ?>
            <div class="alert alert-danger">
                <strong>Perhatian!</strong> Pengajuan izin Anda untuk hari ini telah ditolak oleh administrator. Silakan lakukan absensi "Hadir" jika Anda masuk kerja.
            </div>
        <?php endif; ?>

        <?php if ($hari_libur): ?>
            <div class="alert alert-info mt-4">
                <h4 class="alert-heading">Hari Libur!</h4>
                <p>Nikmati waktu istirahat Anda.</p>
            </div>

        <?php elseif ($status_absensi == "sudah_pulang" || $status_absensi == "izin_diterima" || $status_absensi == "tidak_hadir"): ?>
            <div class="alert alert-primary mt-4">
                <h4 class="alert-heading">Absensi Selesai!</h4>
                <p>Terima kasih, Anda sudah menyelesaikan absensi untuk hari ini.</p>
                <hr>
                <p class="mb-0">
                    Jam Masuk: <strong><?php echo $data_absensi['waktu'] ? date('H:i:s', strtotime($data_absensi['waktu'])) : '-'; ?></strong> |
                    Jam Pulang: <strong><?php echo $data_absensi['waktu_pulang'] ? date('H:i:s', strtotime($data_absensi['waktu_pulang'])) : '-'; ?></strong>
                </p>
            </div>

        <?php elseif ($status_absensi == "menunggu_persetujuan"): ?>
            <div class="alert alert-info mt-4">
                <h4 class="alert-heading">Pengajuan Izin Terkirim</h4>
                <p>Pengajuan izin Anda sedang menunggu persetujuan dari administrator.</p>
            </div>

        <?php elseif ($status_absensi == "sudah_masuk"): ?>
            <div class="alert alert-success mt-4">Anda sudah berhasil absen masuk pada jam: <strong><?php echo date('H:i:s', strtotime($data_absensi['waktu'])); ?></strong></div>

            <?php if ($waktu_sekarang_obj >= $pulang_mulai_obj && $waktu_sekarang_obj <= $pulang_akhir_obj): ?>
                <form action="apps/pengguna/proses_absen.php" method="post" class="mt-4">
                    <input type="hidd   en" name="aksi" value="pulang">
                    <button type="submit" class="btn btn-warning btn-lg shadow-sm"><i class="bi bi-box-arrow-out-right me-2"></i> ABSEN PULANG</button>
                </form>
            <?php else: ?>
                <p class="lead mt-4">Waktu absensi pulang adalah antara jam <?php echo date('H:i', strtotime($setting['pulang_mulai'])); ?> - <?php echo date('H:i', strtotime($setting['pulang_akhir'])); ?></p>
            <?php endif; ?>

        <?php else: // Kondisi utama: Belum Absen 
        ?>
            <?php if ($waktu_sekarang_obj >= $masuk_mulai_obj && $waktu_sekarang_obj <= $masuk_akhir_obj): ?>
                <p class="lead">Silakan pilih status kehadiran Anda:</p>
                <form action="apps/pengguna/proses_absen.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="aksi" value="masuk">
                    <div class="form-group mb-3">
                        <select name="status" id="status" class="form-select" required>
                            <option value="1">Hadir</option>
                            <option value="2">Izin</option>
                            <!-- <option value="3">Tidak Hadir</option> -->
                        </select>
                    </div>
                    <div class="form-group mb-3" id="kolom_alasan" style="display:none;">
                        <!-- <textarea name="alasan" class="form-control" placeholder="Tuliskan alasan izin Anda di sini..."></textarea> -->
                        <label for="alasan" class="form-label float-start">Alasan Izin</label>
                        <textarea name="alasan" id="alasan" class="form-control" placeholder="Tuliskan alasan izin Anda di sini..."></textarea>

                        <label for="file_bukti" class="form-label float-start mt-3">Upload Bukti Izin (Opsional)</label>
                        <input type="file" name="file_bukti" id="file_bukti" class="form-control">
                        <div class="form-text text-start">Format file: PDF, JPG, PNG. Maksimal 2MB.</div>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg shadow-sm"><i class="bi bi-check-circle me-2"></i> Konfirmasi Kehadiran</button>
                </form>
            <?php else: ?>
                <p class="lead mt-4">Waktu absensi masuk adalah antara jam <?php echo date('H:i', strtotime($setting['masuk_mulai'])); ?> - <?php echo date('H:i', strtotime($setting['masuk_akhir'])); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    setInterval(function() {
        var date = new Date();
        var hours = date.getHours().toString().padStart(2, '0');
        var minutes = date.getMinutes().toString().padStart(2, '0');
        var seconds = date.getSeconds().toString().padStart(2, '0');
        document.getElementById('jam-digital').textContent = hours + ":" + minutes + ":" + seconds;
    }, 1000);

    $('#status').on('change', function() {
        if (this.value == '2' || this.value == '3') {
            $('#kolom_alasan').show();
            $('#kolom_alasan textarea').prop('required', true);
            $('#alasan').prop('required', true); // Wajibkan isi alasan
        } else {
            $('#kolom_alasan').hide();
            $('#kolom_alasan textarea').prop('required', false);
            $('#alasan').prop('required', false); // Tidak wajib jika tidak izin
        }
    }).trigger('change');
</script>