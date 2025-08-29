<?php
// Ambil nama pengguna dari session untuk sapaan
$nama_pengguna = ($_SESSION['level'] == 'Admin') ? $_SESSION["nama_admin"] : $_SESSION["nama_mahasiswa"];
$tanggal_hari_ini = date('Y-m-d');

// =======================================================
// LOGIKA PHP UNTUK MENGAMBIL DATA STATISTIK
// =======================================================

// 1. Menghitung jumlah mahasiswa aktif
$sql_mahasiswa_aktif = "SELECT COUNT(id_mahasiswa) as total FROM tbl_mahasiswa WHERE CURDATE() BETWEEN mulai_magang AND akhir_magang";
$hasil_mahasiswa_aktif = mysqli_query($kon, $sql_mahasiswa_aktif);
$data_mahasiswa_aktif = mysqli_fetch_assoc($hasil_mahasiswa_aktif);
$jumlah_mahasiswa_aktif = $data_mahasiswa_aktif['total'];

// 2. Menghitung statistik kehadiran hari ini
$sql_kehadiran = "SELECT 
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as izin
                  FROM tbl_absensi 
                  WHERE tanggal = '$tanggal_hari_ini'";
$hasil_kehadiran = mysqli_query($kon, $sql_kehadiran);
$data_kehadiran = mysqli_fetch_assoc($hasil_kehadiran);
$jumlah_hadir_hari_ini = $data_kehadiran['hadir'] ?? 0;
$jumlah_izin_hari_ini = $data_kehadiran['izin'] ?? 0;

// 3. Menghitung jumlah yang belum absen hari ini
$jumlah_sudah_absen = $jumlah_hadir_hari_ini + $jumlah_izin_hari_ini;
$jumlah_belum_absen = $jumlah_mahasiswa_aktif - $jumlah_sudah_absen;

// =======================================================
// LOGIKA PHP UNTUK DATA GRAFIK (7 HARI TERAKHIR)
// =======================================================
$labels_grafik = [];
$data_hadir_grafik = [];
$data_izin_grafik = [];

for ($i = 6; $i >= 0; $i--) {
    $tanggal = date('Y-m-d', strtotime("-$i days"));
    $labels_grafik[] = date('d/m', strtotime($tanggal)); // Format label (misal: 29/08)

    $sql_grafik = "SELECT 
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as izin
                   FROM tbl_absensi WHERE tanggal = '$tanggal'";
    
    $hasil_grafik = mysqli_query($kon, $sql_grafik);
    $data_per_hari = mysqli_fetch_assoc($hasil_grafik);
    
    $data_hadir_grafik[] = $data_per_hari['hadir'] ?? 0;
    $data_izin_grafik[] = $data_per_hari['izin'] ?? 0;
}
?>

<!-- [BARU] Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Beranda</li>
    </ol>
</nav>

<!-- [DIUBAH] Pesan Selamat Datang -->
<div class="alert alert-light" role="alert">
    <h4 class="alert-heading">Selamat Datang, <?php echo htmlspecialchars($nama_pengguna); ?>!</h4>
    <p>Ini adalah ringkasan aktivitas magang di sistem. Anda dapat memantau kehadiran dan data lainnya melalui menu di samping.</p>
</div>
<<<<<<< HEAD

<div class="panel panel-container">

            <!--Menampilkan Nama Pengguna Sesuai Level -->
            <?php if ($_SESSION['level']=='Admin' or $_SESSION['level']=='Admin'):?>
            <center><h2><b><br>Selamat Datang</b>,  <?php echo  $_SESSION["nama_admin"]; ?>.</h2><br><br><br></center>
            <?php endif; ?>
            <?php if ($_SESSION['level']=='Mahasiswa' or $_SESSION['level']=='mahasiswa'):?>
            <center> <h2><b><br>Selamat Datang</b>, <?php echo  $_SESSION["nama_mahasiswa"]; ?>.</h2><br><br><br></center>
            <?php endif; ?>
            <!-- Menampilkan Nama Pengguna Sesuai Level -->

<div class="card text-center">
    <div class="card-body">
    
            <!-- Mengambil data table tbl_site -->
            <?php 
                //Mengambil profil aplikasi
                //Mengubungkan database
                include 'config/database.php';
                $query = mysqli_query($kon, "select * from tbl_site limit 1");    
                $row = mysqli_fetch_array($query);
            ?>
            <!-- Menhambil data table tbl_site -->

            <br><img src="logo.png" alt="logo" width="150">
            <br>
            <br>
            <br>
            <br>
            <br>

            <!-- Info Aplikasi -->
            <center><p>Selamat Datang di<b> Aplikasi Absensi dan Kegiatan Harian Mahasiswa </b>berbasis web. 
                <br>Aplikasi ini dirancang untuk memudahkan Mahasiswa PKL di<b> <?php echo $row['nama_instansi'];?> </b>
                <br>dalam melakukan absensi serta mencatat kegiatan harian secara digital, cepat, dan praktis, 
                sehingga <br>memudahkan mahasiswa maupun pembimbing dalam mengelola 
                dan memantau aktivitas selama masa PKL.</center>
            <!-- Info Aplikasi -->
            
=======

<!-- [BARU] Kartu Statistik -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fs-2"><?php echo $jumlah_mahasiswa_aktif; ?></h5>
                    <p class="card-text">Mahasiswa Aktif</p>
                </div>
                <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.5;"></i>
>>>>>>> main
            </div>
        </div>
    </div>
    <div class="col-md-3">
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
    <div class="col-md-3">
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
    <div class="col-md-3">
        <div class="card text-white bg-danger h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fs-2"><?php echo $jumlah_belum_absen; ?></h5>
                    <p class="card-text">Belum Absen</p>
                </div>
                <i class="bi bi-question-circle-fill" style="font-size: 3rem; opacity: 0.5;"></i>
            </div>
        </div>
    </div>
</div>

<!-- [BARU] Grafik Kehadiran -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Grafik Kehadiran 7 Hari Terakhir</h5>
    </div>
    <div class="card-body">
        <canvas id="grafikKehadiran"></canvas>
    </div>
</div>

<!-- [BARU] JavaScript untuk menginisialisasi Chart.js -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('grafikKehadiran').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar', // Tipe grafik: batang
        data: {
            // Mengambil label dari PHP
            labels: <?php echo json_encode($labels_grafik); ?>,
            datasets: [
                {
                    label: 'Hadir',
                    // Mengambil data hadir dari PHP
                    data: <?php echo json_encode($data_hadir_grafik); ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)', // Warna hijau
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Izin',
                    // Mengambil data izin dari PHP
                    data: <?php echo json_encode($data_izin_grafik); ?>,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)', // Warna kuning
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Jumlah Mahasiswa Berdasarkan Status Kehadiran'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        // Memastikan sumbu Y hanya menampilkan angka bulat
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>