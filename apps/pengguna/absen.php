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

// Ambil data absensi hari ini
$stmt_absen = mysqli_prepare($kon, "SELECT waktu, waktu_pulang, status FROM tbl_absensi WHERE id_mahasiswa = ? AND tanggal = ?");
mysqli_stmt_bind_param($stmt_absen, "is", $id_mahasiswa, $tanggal_hari_ini);
mysqli_stmt_execute($stmt_absen);
$data_absensi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_absen));

// Ambil pengaturan waktu
$hasil_setting = mysqli_query($kon, "SELECT * FROM tbl_setting_absensi LIMIT 1");
$setting = mysqli_fetch_assoc($hasil_setting);

// Konversi waktu ke objek DateTime
$waktu_sekarang_obj = new DateTime();
$masuk_mulai_obj = new DateTime($setting['masuk_mulai']);
$masuk_akhir_obj = new DateTime($setting['masuk_akhir']);
$pulang_mulai_obj = new DateTime($setting['pulang_mulai']);
$pulang_akhir_obj = new DateTime($setting['pulang_akhir']);

// Logika status
$status_absensi = "belum_absen";
$izin_ditolak = false;
if ($data_absensi) {
    $status_db = $data_absensi['status'];
    if ($status_db == 2) {
        $status_absensi = "izin_diterima";
    } elseif ($status_db == 3) {
        $status_absensi = "tidak_hadir";
    } elseif ($status_db == 4) {
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

$tanggal_indonesia = MendapatkanHari(date('l')) . ", " . date('d') . " " . MendapatkanBulan(date('m')) . " " . date('Y');
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
        <!-- [TAMBAHKAN BLOK NOTIFIKASI INI] -->
        <?php
        // Notifikasi untuk error
        if (isset($_GET['error'])) {
            $pesan_error = '';
            if ($_GET['error'] == 'jarak_terlalu_jauh') {
                $pesan_error = 'Absensi Gagal! Anda berada terlalu jauh dari lokasi kantor.';
            }
            // Anda bisa tambahkan `elseif` lain untuk error di masa depan

            if (!empty($pesan_error)) {
                echo "<div class='alert alert-danger'>{$pesan_error}</div>";
            }
        }

        // Notifikasi untuk sukses
        if (isset($_GET['absen'])) {
            $pesan_sukses = '';
            if ($_GET['absen'] == 'sukses_masuk') {
                $pesan_sukses = 'Absen masuk berhasil direkam. Jangan lupa absen pulang nanti.';
            } elseif ($_GET['absen'] == 'sukses_izin') {
                $pesan_sukses = 'Pengajuan izin Anda telah terkirim.';
            } elseif ($_GET['absen'] == 'sukses_pulang') {
                $pesan_sukses = 'Absen pulang berhasil direkam. Terima kasih.';
            }

            if (!empty($pesan_sukses)) {
                echo "<div class='alert alert-success'>{$pesan_sukses}</div>";
            }
        }
        ?>

        <?php if ($izin_ditolak): ?>
            <div class="alert alert-danger"><strong>Perhatian!</strong> Pengajuan izin Anda telah ditolak. Silakan lakukan absensi "Hadir" jika Anda masuk kerja.</div>
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
            </div>
        <?php elseif ($status_absensi == "menunggu_persetujuan"): ?>
            <div class="alert alert-info mt-4">
                <h4 class="alert-heading">Pengajuan Izin Terkirim</h4>
                <p>Pengajuan izin Anda sedang menunggu persetujuan.</p>
            </div>
        <?php elseif ($status_absensi == "sudah_masuk"): ?>
            <div class="alert alert-success mt-4">Anda sudah berhasil absen masuk pada jam: <strong><?php echo date('H:i:s', strtotime($data_absensi['waktu'])); ?></strong></div>
            <?php if ($waktu_sekarang_obj >= $pulang_mulai_obj && $waktu_sekarang_obj <= $pulang_akhir_obj): ?>
                <form action="apps/pengguna/proses_absen.php" method="post" class="mt-4">
                    <input type="hidden" name="aksi" value="pulang">
                    <button type="submit" class="btn btn-warning btn-lg shadow-sm"><i class="bi bi-box-arrow-out-right me-2"></i> ABSEN PULANG</button>
                </form>
            <?php else: ?>
                <p class="lead mt-4">Waktu absensi pulang adalah antara jam <?php echo date('H:i', strtotime($setting['pulang_mulai'])); ?> - <?php echo date('H:i', strtotime($setting['pulang_akhir'])); ?></p>
            <?php endif; ?>
        <?php else: // Kondisi utama: Belum Absen (termasuk Izin Ditolak) 
        ?>
            <?php if ($waktu_sekarang_obj >= $masuk_mulai_obj && $waktu_sekarang_obj <= $masuk_akhir_obj): ?>
                <p class="lead">Silakan lakukan absensi untuk hari ini.</p>
                <!-- Tombol-tombol Aksi -->
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <button type="button" class="btn btn-success btn-lg px-4 gap-3" data-bs-toggle="modal" data-bs-target="#modalAbsenHadir">
                        <i class="bi bi-camera-fill"></i> Hadir
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-lg px-4" data-bs-toggle="modal" data-bs-target="#modalAbsenIzin">
                        <i class="bi bi-envelope-fill"></i> Ajukan Izin
                    </button>
                </div>
            <?php else: ?>
                <p class="lead mt-4">Waktu absensi masuk adalah antara jam <?php echo date('H:i', strtotime($setting['masuk_mulai'])); ?> - <?php echo date('H:i', strtotime($setting['masuk_akhir'])); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL-MODAL -->
<!-- ======================================================= -->

<!-- Modal untuk Absen Hadir (Kamera & Lokasi) -->
<div class="modal fade" id="modalAbsenHadir" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Absen Hadir - Foto & Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center" id="pesan-status">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Meminta izin kamera dan lokasi...</p>
                </div>
                <video id="kamera-video" autoplay playsinline class="w-100 rounded" style="display: none; transform: scaleX(-1);"></video>
                <canvas id="kamera-canvas" style="display:none;"></canvas>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-primary btn-lg" id="tombol-ambil-foto" disabled>
                    <i class="bi bi-camera-fill"></i> Ambil Foto & Absen
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Absen Izin -->
<div class="modal fade" id="modalAbsenIzin" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Formulir Pengajuan Izin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="apps/pengguna/proses_absen.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="masuk_izin">
                    <div class="mb-3">
                        <label for="alasan" class="form-label">Alasan Izin</label>
                        <textarea name="alasan" id="alasan" class="form-control" placeholder="Tuliskan alasan izin Anda di sini..." required></textarea>
                    </div>
                    <div>
                        <label for="file_bukti" class="form-label">Upload Bukti Izin (Opsional)</label>
                        <input type="file" name="file_bukti" id="file_bukti" class="form-control">
                        <div class="form-text">Format: PDF, JPG, PNG. Maksimal 2MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form tersembunyi untuk Absen Hadir -->
<form action="apps/pengguna/proses_absen.php" method="post" id="form-absen-hadir" style="display:none;">
    <input type="hidden" name="aksi" value="masuk_hadir">
    <input type="hidden" name="foto_base64" id="input-foto-base64">
    <input type="hidden" name="latitude" id="input-latitude">
    <input type="hidden" name="longitude" id="input-longitude">
</form>

<!-- ======================================================= -->
<!-- BAGIAN SCRIPT LENGKAP -->
<!-- ======================================================= -->
<script>
    $(document).ready(function() {

        // --- BAGIAN 1: SCRIPT YANG SUDAH ADA (JAM & FORM) ---

        // Jam digital
        setInterval(function() {
            var date = new Date();
            var hours = date.getHours().toString().padStart(2, '0');
            var minutes = date.getMinutes().toString().padStart(2, '0');
            var seconds = date.getSeconds().toString().padStart(2, '0');
            // Pastikan elemen jam-digital ada sebelum mengubahnya
            const jamDigitalElement = document.getElementById('jam-digital');
            if (jamDigitalElement) {
                jamDigitalElement.textContent = hours + ":" + minutes + ":" + seconds;
            }
        }, 1000);

        // Tampilkan/Sembunyikan kolom alasan untuk form Izin
        $('#status').on('change', function() {
            if (this.value == '2') {
                $('#kolom_alasan').show();
                $('#kolom_alasan textarea').prop('required', true);
            } else {
                $('#kolom_alasan').hide();
                $('#kolom_alasan textarea').prop('required', false);
            }
        }).trigger('change');


        // --- BAGIAN 2: SCRIPT BARU (KAMERA & GEOLOCATION) ---

        const modalAbsenHadir = document.getElementById('modalAbsenHadir');
        const videoElement = document.getElementById('kamera-video');
        const canvasElement = document.getElementById('kamera-canvas');
        const tombolAmbilFoto = document.getElementById('tombol-ambil-foto');
        const pesanStatus = document.getElementById('pesan-status');

        let userLatitude = null;
        let userLongitude = null;
        let stream = null;

        // Event ini berjalan saat modal kamera akan ditampilkan
        if (modalAbsenHadir) {
            modalAbsenHadir.addEventListener('show.bs.modal', function() {

                const promiseKamera = navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user'
                    }
                });

                const promiseLokasi = new Promise((resolve, reject) => {
                    if (!navigator.geolocation) {
                        return reject(new Error("Geolocation tidak didukung."));
                    }
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    });
                });

                Promise.all([promiseKamera, promiseLokasi])
                    .then(([streamKamera, posisiLokasi]) => {
                        stream = streamKamera;
                        videoElement.srcObject = stream;
                        videoElement.style.display = 'block';

                        userLatitude = posisiLokasi.coords.latitude;
                        userLongitude = posisiLokasi.coords.longitude;

                        pesanStatus.style.display = 'none';
                        tombolAmbilFoto.disabled = false;
                    })
                    .catch((err) => {
                        let pesanError = "Terjadi kesalahan.";
                        if (err.name === "NotAllowedError" || err.name === "PermissionDeniedError") {
                            pesanError = "Anda harus memberikan izin akses kamera dan lokasi untuk melanjutkan.";
                        } else if (err.code === 1 || err.message.includes("Geolocation")) { // Kode 1 adalah permission denied untuk Geolocation
                            pesanError = "Tidak dapat mendeteksi lokasi Anda. Pastikan GPS aktif dan Anda memberikan izin.";
                        } else {
                            pesanError = "Tidak dapat mengakses kamera. Pastikan tidak digunakan oleh aplikasi lain.";
                        }
                        pesanStatus.innerHTML = `<p class="text-danger"><strong>Error:</strong> ${pesanError}</p>`;
                    });
            });

            // Event ini berjalan saat modal ditutup
            modalAbsenHadir.addEventListener('hidden.bs.modal', function() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
                videoElement.style.display = 'none';
                pesanStatus.style.display = 'block';
                pesanStatus.innerHTML = '<div class="spinner-border text-primary"></div><p class="mt-2">Meminta izin kamera dan lokasi...</p>';
                tombolAmbilFoto.disabled = true;
            });
        }

        // Aksi saat tombol "Ambil Foto" diklik
        if (tombolAmbilFoto) {
            tombolAmbilFoto.addEventListener('click', function() {
                canvasElement.width = videoElement.videoWidth;
                canvasElement.height = videoElement.videoHeight;

                const context = canvasElement.getContext('2d');
                context.translate(canvasElement.width, 0);
                context.scale(-1, 1);
                context.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

                const dataUrl = canvasElement.toDataURL('image/jpeg', 0.9); // Kompresi gambar sedikit

                document.getElementById('input-foto-base64').value = dataUrl;
                document.getElementById('input-latitude').value = userLatitude;
                document.getElementById('input-longitude').value = userLongitude;

                document.getElementById('form-absen-hadir').submit();
            });
        }

    });
</script>