<?php
// ===================================================================
// KODE INI AKAN MENENTUKAN DASHBOARD MANA YANG TAMPIL
// ===================================================================

if ($_SESSION['level'] == 'Admin') :
    // =======================================================
    // TAMPILAN DASHBOARD UNTUK ADMIN (VERSI FINAL)
    // =======================================================
    $tanggal_hari_ini = date('Y-m-d');

    // 1. Menghitung jumlah mahasiswa aktif
    $sql_mahasiswa_aktif = "SELECT COUNT(id_mahasiswa) as total FROM tbl_mahasiswa WHERE CURDATE() BETWEEN mulai_magang AND akhir_magang";
    $data_mahasiswa_aktif = mysqli_fetch_assoc(mysqli_query($kon, $sql_mahasiswa_aktif));
    $jumlah_mahasiswa_aktif = $data_mahasiswa_aktif['total'];

    // 2. Menghitung statistik kehadiran hari ini
    $sql_kehadiran = "SELECT 
                        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as hadir, 
                        SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as tidak_hadir
                      FROM tbl_absensi WHERE tanggal = '$tanggal_hari_ini'";
    $data_kehadiran = mysqli_fetch_assoc(mysqli_query($kon, $sql_kehadiran));
    $jumlah_hadir_hari_ini = $data_kehadiran['hadir'] ?? 0;
    $jumlah_izin_hari_ini = $data_kehadiran['izin'] ?? 0;
    $jumlah_tidak_hadir_hari_ini = $data_kehadiran['tidak_hadir'] ?? 0;

    // 3. Menghitung jumlah yang belum absen
    $jumlah_sudah_absen = $jumlah_hadir_hari_ini + $jumlah_izin_hari_ini + $jumlah_tidak_hadir_hari_ini;
    $jumlah_belum_absen = $jumlah_mahasiswa_aktif - $jumlah_sudah_absen;
    // [PERBAIKAN] Pastikan tidak pernah negatif
    if ($jumlah_belum_absen < 0) {
        $jumlah_belum_absen = 0;
    }

    // 4. Data untuk Grafik
    $labels_grafik = [];
    $data_hadir_grafik = [];
    $data_izin_grafik = [];
    $data_tidak_hadir_grafik = [];
    for ($i = 6; $i >= 0; $i--) {
        $tanggal = date('Y-m-d', strtotime("-$i days"));
        $labels_grafik[] = date('d/m', strtotime($tanggal));
        $sql_grafik = "SELECT 
                        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as hadir, 
                        SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as tidak_hadir
                       FROM tbl_absensi WHERE tanggal = '$tanggal'";
        $data_per_hari = mysqli_fetch_assoc(mysqli_query($kon, $sql_grafik));
        $data_hadir_grafik[] = $data_per_hari['hadir'] ?? 0;
        $data_izin_grafik[] = $data_per_hari['izin'] ?? 0;
        $data_tidak_hadir_grafik[] = $data_per_hari['tidak_hadir'] ?? 0;
    }
?>
    <!-- Tampilan HTML untuk Admin -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Beranda</li>
        </ol>
    </nav>
    <div class="alert alert-light">
        <h4 class="alert-heading">Selamat Datang, <?php echo htmlspecialchars($_SESSION["nama_admin"]); ?>!</h4>
        <p>Ini adalah ringkasan aktivitas magang. Anda dapat memantau kehadiran dan data lainnya melalui menu di samping.</p>
    </div>

    <!-- [PERBAIKAN] Menggunakan grid Bootstrap standar untuk 4 kartu agar simetris -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card text-white bg-primary h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title fs-2"><?php echo $jumlah_mahasiswa_aktif; ?></h5>
                        <p class="card-text">Peserta Aktif</p>
                    </div>
                    <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card text-white bg-success h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title fs-2"><?php echo $jumlah_hadir_hari_ini; ?></h5>
                        <p class="card-text">Hadir Hari Ini</p>
                    </div>
                    <i class="bi bi-check-circle-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card text-white bg-warning h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title fs-2"><?php echo $jumlah_izin_hari_ini; ?></h5>
                        <p class="card-text">Izin Hari Ini</p>
                    </div>
                    <i class="bi bi-exclamation-circle-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
        <!-- [DIUBAH] Menggabungkan "Tidak Hadir" dan "Belum Absen" -->
        <div class="col-lg-3 col-md-6">
            <div class="card text-white bg-danger h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title fs-2"><?php echo $jumlah_tidak_hadir_hari_ini + $jumlah_belum_absen; ?></h5>
                        <p class="card-text">Tidak Hadir / Belum Absen</p>
                    </div>
                    <i class="bi bi-x-circle-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Grafik Kehadiran 7 Hari Terakhir</h5>
        </div>
        <div class="card-body">
            <canvas id="grafikKehadiran"></canvas>
        </div>
    </div>


    <!-- [DIUBAH] Script Chart.js diperbarui untuk menampilkan data "Tidak Hadir" -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('grafikKehadiran').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels_grafik); ?>,
                    datasets: [{
                            label: 'Hadir',
                            data: <?php echo json_encode($data_hadir_grafik); ?>,
                            backgroundColor: 'rgba(40, 167, 69, 0.7)' // Hijau
                        },
                        {
                            label: 'Izin',
                            data: <?php echo json_encode($data_izin_grafik); ?>,
                            backgroundColor: 'rgba(255, 193, 7, 0.7)' // Kuning
                        },
                        {
                            label: 'Tidak Hadir',
                            data: <?php echo json_encode($data_tidak_hadir_grafik); ?>,
                            backgroundColor: 'rgba(108, 117, 125, 0.7)' // Abu-abu
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Rekapitulasi Kehadiran Mahasiswa per Hari'
                        }
                    },
                    scales: {
                        // [PERBAIKAN] Opsi 'stacked: true' telah dihapus
                        x: {},
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1 // Memastikan sumbu Y hanya menampilkan angka bulat
                            }
                        }
                    }
                }
            });
        });
    </script>

<?php else:
    // =======================================================
    // TAMPILAN DASHBOARD BARU UNTUK MAHASISWA
    // =======================================================
    $id_mahasiswa = $_SESSION['id_mahasiswa'];
    $tanggal_hari_ini = date("Y-m-d");

    // 1. Ambil data statistik personal
    $stmt_stats = mysqli_prepare($kon, "SELECT 
        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as total_hadir,
        SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as total_izin,
        SUM(CASE WHEN keterangan LIKE 'Terlambat%' THEN 1 ELSE 0 END) as total_terlambat
        FROM tbl_absensi WHERE id_mahasiswa = ?");
    mysqli_stmt_bind_param($stmt_stats, "i", $id_mahasiswa);
    mysqli_stmt_execute($stmt_stats);
    $data_stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_stats));

    $stmt_kegiatan = mysqli_prepare($kon, "SELECT COUNT(id_kegiatan) as total FROM tbl_kegiatan WHERE id_mahasiswa = ?");
    mysqli_stmt_bind_param($stmt_kegiatan, "i", $id_mahasiswa);
    mysqli_stmt_execute($stmt_kegiatan);
    $data_kegiatan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_kegiatan));

    // 2. Ambil info periode magang & hitung sisa hari
    $stmt_periode = mysqli_prepare($kon, "SELECT mulai_magang, akhir_magang FROM tbl_mahasiswa WHERE id_mahasiswa = ?");
    mysqli_stmt_bind_param($stmt_periode, "i", $id_mahasiswa);
    mysqli_stmt_execute($stmt_periode);
    $data_periode = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_periode));

    $tgl_mulai = new DateTime($data_periode['mulai_magang']);
    $tgl_akhir = new DateTime($data_periode['akhir_magang']);
    $tgl_sekarang = new DateTime();
    $total_durasi = $tgl_akhir->diff($tgl_mulai)->days;
    $sudah_berjalan = $tgl_sekarang->diff($tgl_mulai)->days;
    $persentase_progres = ($total_durasi > 0) ? round(($sudah_berjalan / $total_durasi) * 100) : 0;
    if ($persentase_progres > 100) $persentase_progres = 100;

    // 3. Cek status absensi hari ini untuk Panel Aksi Cepat
    $stmt_absen_hari_ini = mysqli_prepare($kon, "SELECT waktu, waktu_pulang, status FROM tbl_absensi WHERE id_mahasiswa = ? AND tanggal = ?");
    mysqli_stmt_bind_param($stmt_absen_hari_ini, "is", $id_mahasiswa, $tanggal_hari_ini);
    mysqli_stmt_execute($stmt_absen_hari_ini);
    $data_absen_hari_ini = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_absen_hari_ini));
    $status_absensi_hari_ini = "belum_absen";
    if ($data_absen_hari_ini) {
        if ($data_absen_hari_ini['waktu_pulang']) $status_absensi_hari_ini = "sudah_pulang";
        elseif ($data_absen_hari_ini['waktu']) $status_absensi_hari_ini = "sudah_masuk";
        if ($data_absen_hari_ini['status'] == 2) $status_absensi_hari_ini = "izin";
    }
?>
    <!-- Tampilan HTML untuk Mahasiswa -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Beranda</li>
        </ol>
    </nav>
    <div class="alert alert-light">
        <h4 class="alert-heading">Selamat Datang, <?php echo htmlspecialchars($_SESSION["nama_mahasiswa"]); ?>!</h4>
        <p>Dashboard personal Anda. Pantau progres dan lakukan absensi serta pencatatan kegiatan harian di sini.</p>
    </div>

    <div class="row">
        <!-- Kolom Kiri: Aksi Cepat & Info Periode -->
        <div class="col-lg-7">
            <!-- Panel Aksi Cepat -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Progres Hari Ini</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($status_absensi_hari_ini == "belum_absen"): ?>
                        <p>Anda belum melakukan absensi hari ini.</p>
                        <a href="index.php?page=absen" class="btn btn-success btn-lg"><i class="bi bi-box-arrow-in-right me-2"></i> Lakukan Absensi Masuk</a>
                    <?php elseif ($status_absensi_hari_ini == "sudah_masuk"): ?>
                        <p>Anda sudah absen masuk. Jangan lupa absen pulang.</p>
                        <a href="index.php?page=absen" class="btn btn-warning btn-lg"><i class="bi bi-box-arrow-out-right me-2"></i> Lakukan Absensi Pulang</a>
                    <?php else: ?>
                        <p class="text-muted">Absensi hari ini sudah selesai. Terima kasih.</p>
                    <?php endif; ?>
                    <hr>
                    <a href="index.php?page=kegiatan" class="btn btn-outline-primary"><i class="bi bi-journal-plus me-2"></i> Tambah Laporan Kegiatan</a>
                </div>
            </div>
            <!-- Info Periode Magang -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Progres Periode Magang</h5>
                </div>
                <div class="card-body">
                    <p>Periode magang Anda: <strong><?php echo date('d M Y', strtotime($data_periode['mulai_magang'])); ?></strong> s/d <strong><?php echo date('d M Y', strtotime($data_periode['akhir_magang'])); ?></strong></p>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $persentase_progres; ?>%;" aria-valuenow="<?php echo $persentase_progres; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $persentase_progres; ?>% Selesai</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Kolom Kanan: Kartu Statistik Personal -->
        <div class="col-lg-5">
            <div class="row g-3">
                <div class="col-6">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h4 class="mb-0"><?php echo $data_stats['total_hadir'] ?? 0; ?></h4><small class="text-muted">Total Hari Hadir</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h4 class="mb-0"><?php echo $data_stats['total_izin'] ?? 0; ?></h4><small class="text-muted">Total Hari Izin</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h4 class="mb-0"><?php echo $data_stats['total_terlambat'] ?? 0; ?></h4><small class="text-muted">Jumlah Terlambat</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h4 class="mb-0"><?php echo $data_kegiatan['total'] ?? 0; ?></h4><small class="text-muted">Kegiatan Dicatat</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>