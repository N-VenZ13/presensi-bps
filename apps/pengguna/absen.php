<?php
date_default_timezone_set('Asia/Jakarta');

if ($_SESSION["level"] != 'Mahasiswa') {
    exit;
}

include 'config/function.php'; // Pastikan file fungsi disertakan

$id_mahasiswa = $_SESSION['id_mahasiswa'];
$tanggal_hari_ini = date("Y-m-d");
$hari_ini = strtolower(date("l"));
$hari_libur = ($hari_ini == "saturday" || $hari_ini == "sunday");

// Ambil data absensi hari ini
$stmt_absen = mysqli_prepare($kon, "SELECT waktu, waktu_pulang, status FROM tbl_absensi WHERE id_mahasiswa = ? AND tanggal = ?");
mysqli_stmt_bind_param($stmt_absen, "is", $id_mahasiswa, $tanggal_hari_ini);
mysqli_stmt_execute($stmt_absen);
$data_absensi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_absen));

// Ambil pengaturan waktu
$hasil_setting = mysqli_query($kon, "SELECT * FROM tbl_setting_absensi LIMIT 1");
$setting = mysqli_fetch_assoc($hasil_setting);

// Konversi waktu ke objek DateTime untuk perbandingan yang akurat
$waktu_sekarang_obj = new DateTime();
$masuk_mulai_obj = new DateTime($setting['masuk_mulai']);
$masuk_akhir_obj = new DateTime($setting['masuk_akhir']);
$pulang_mulai_obj = new DateTime($setting['pulang_mulai']);
$pulang_akhir_obj = new DateTime($setting['pulang_akhir']);

// debug


// Tentukan status absensi utama
$status_absensi = "belum_absen";
if ($data_absensi) {
    // Cek dulu apakah statusnya sudah "selesai" (Izin atau Tidak Hadir)
    if ($data_absensi['status'] == 2) {
        $status_absensi = "izin";
    } elseif ($data_absensi['status'] == 3) {
        $status_absensi = "tidak_hadir";
    }
    // Jika bukan Izin atau Tidak Hadir, baru cek jam masuk/pulang
    else {
        if ($data_absensi['waktu_pulang']) {
            $status_absensi = "sudah_pulang";
        } elseif ($data_absensi['waktu']) {
            $status_absensi = "sudah_masuk";
        }
    }
}

// Siapkan tanggal dalam Bahasa Indonesia
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

        <?php if ($hari_libur): ?>
            <div class="alert alert-info mt-4">
                <h4 class="alert-heading">Hari Libur!</h4>
                <p>Nikmati waktu istirahat Anda.</p>
            </div>

        <?php elseif ($status_absensi == "sudah_pulang" || $status_absensi == "izin" || $status_absensi == "tidak_hadir"): ?>
            <div class="alert alert-primary mt-4">
                <h4 class="alert-heading">Absensi Selesai!</h4>
                <p>Terima kasih, Anda sudah menyelesaikan absensi untuk hari ini.</p>
                <hr>
                <p class="mb-0">
                    Jam Masuk: <strong><?php echo $data_absensi['waktu'] ? date('H:i:s', strtotime($data_absensi['waktu'])) : '-'; ?></strong> |
                    Jam Pulang: <strong><?php echo $data_absensi['waktu_pulang'] ? date('H:i:s', strtotime($data_absensi['waktu_pulang'])) : '-'; ?></strong>
                </p>
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
                <form action="apps/pengguna/proses_absen.php" method="post">
                    <input type="hidden" name="aksi" value="masuk">
                    <div class="form-group mb-3">
                        <select name="status" id="status" class="form-select" required>
                            <option value="1">Hadir</option>
                            <option value="2">Izin</option>
                            <option value="3">Tidak Hadir</option>
                        </select>
                    </div>
                    <div class="form-group mb-3" id="kolom_alasan" style="display:none;">
                        <textarea name="alasan" class="form-control" placeholder="Tuliskan alasan izin Anda di sini..."></textarea>
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
        } else {
            $('#kolom_alasan').hide();
            $('#kolom_alasan textarea').prop('required', false);
        }
    }).trigger('change');
</script>