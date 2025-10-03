<?php
if ($_SESSION["level"] != 'Admin') {
    echo "<div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
include 'config/function.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Data Absensi</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Absensi</h5>
        <div>
            <button type="button" class="btn btn-secondary" id="tombol_cetak_laporan">
                <i class="bi bi-printer-fill me-1"></i> Cetak Laporan
            </button>
            <button type="button" class="btn btn-primary" id="tambah_absensi">
                <i class="bi bi-plus-lg me-1"></i> Tambah Absensi Manual
            </button>
        </div>
    </div>
    <div class="card-body">

        <!-- Notifikasi -->
        <?php
        if (isset($_GET['mulai'])) {
            if ($_GET['mulai'] == 'berhasil') echo "<div class='alert alert-success'><strong>Berhasil!</strong> Data absensi berhasil ditambahkan.</div>";
            else if ($_GET['mulai'] == 'gagal') echo "<div class='alert alert-warning'><strong>Gagal!</strong> Data absensi sudah ada.</div>";
        }
        // [BARU] Notifikasi untuk persetujuan izin
        if (isset($_GET['approve'])) {
            if ($_GET['approve'] == 'berhasil') echo "<div class='alert alert-success'><strong>Berhasil!</strong> Status izin telah diperbarui.</div>";
            else echo "<div class='alert alert-danger'><strong>Gagal!</strong> Status izin gagal diperbarui.</div>";
        }
        ?>

        <!-- Form Filter -->
        <div class="p-3 mb-4 rounded" style="background-color: #f8f9fa;">
            <form action="index.php" method="GET">
                <input type="hidden" name="page" value="data_absensi" />
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="nama" class="form-label">Nama Peserta</label>
                        <input type="text" name="nama" id="nama" class="form-control" placeholder="Cari nama..." value="<?php echo isset($_GET['nama']) ? htmlspecialchars($_GET['nama']) : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="tanggal_awal" class="form-label">Dari Tanggal</label>
                        <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" value="<?php echo isset($_GET['tanggal_awal']) ? htmlspecialchars($_GET['tanggal_awal']) : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="tanggal_akhir" class="form-label">Sampai Tanggal</label>
                        <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" value="<?php echo isset($_GET['tanggal_akhir']) ? htmlspecialchars($_GET['tanggal_akhir']) : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Hari, Tanggal</th>
                        <th>Keterangan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // [PERBAIKAN] Query diperbarui untuk JOIN ke tbl_alasan
                    $base_sql = "SELECT 
                                    a.*, 
                                    m.nama, 
                                    m.nama_instansi_asal, 
                                    al.alasan, 
                                    al.file_bukti,
                
                                    CASE a.status 
                                        WHEN 1 THEN 'Hadir' 
                                        WHEN 2 THEN 'Izin Disetujui' 
                                        WHEN 3 THEN 'Tidak Hadir'
                                        WHEN 4 THEN 'Izin Ditolak'
                                        WHEN 5 THEN 'Menunggu Persetujuan'
                                        ELSE 'N/A' 
                                    END as status_text 
                                FROM tbl_absensi a 
                                JOIN tbl_mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa
                                LEFT JOIN tbl_alasan al ON a.id_mahasiswa = al.id_mahasiswa AND a.tanggal = al.tanggal";


                    $conditions = [];
                    $params = [];
                    $types = "";

                    if (!empty($_GET['nama'])) {
                        $conditions[] = "m.nama LIKE ?";
                        $params[] = "%" . $_GET['nama'] . "%";
                        $types .= "s";
                    }
                    if (!empty($_GET['tanggal_awal'])) {
                        $conditions[] = "a.tanggal >= ?";
                        $params[] = $_GET['tanggal_awal'];
                        $types .= "s";
                    }
                    if (!empty($_GET['tanggal_akhir'])) {
                        $conditions[] = "a.tanggal <= ?";
                        $params[] = $_GET['tanggal_akhir'];
                        $types .= "s";
                    }

                    if (!empty($conditions)) {
                        $base_sql .= " WHERE " . implode(" AND ", $conditions);
                    }

                    $base_sql .= " ORDER BY a.tanggal DESC, a.waktu DESC";

                    $stmt = mysqli_prepare($kon, $base_sql);
                    if (!empty($params)) {
                        mysqli_stmt_bind_param($stmt, $types, ...$params);
                    }
                    mysqli_stmt_execute($stmt);

                    $hasil = mysqli_stmt_get_result($stmt);
                    $no = 0;
                    while ($data = mysqli_fetch_array($hasil)):
                        $no++;

                        // =======================================================
                        // TAMBAHKAN BLOK DEBUG DI SINI
                        // =======================================================
                        // if ($no == 1) { // Hanya dump data baris pertama
                        //     echo "<pre style='background: #111; color: #eee; padding: 10px; border: 1px solid #ccc;'>";
                        //     echo "<strong>--- DEBUG: ISI ARRAY \$data DARI DATABASE ---</strong><br>";
                        //     var_dump($data);
                        //     echo "</pre>";
                        // }
                        // =======================================================
                        // AKHIR BLOK DEBUG
                        // =======================================================

                        // [PERBAIKAN] Logika baru untuk badge status
                        $baris_class = '';
                        $badge_color = 'secondary'; // Warna default
                        switch ($data['status']) {
                            case 1:
                                $badge_color = 'success';
                                break;
                            case 2:
                                $badge_color = 'info';
                                break;
                            case 3:
                                $badge_color = 'dark';
                                break;
                            case 4:
                                $badge_color = 'danger';
                                break;
                            case 5:
                                $badge_color = 'warning text-dark';
                                $baris_class = 'table-warning';
                                break;
                        }
                        $status_badge = "<span class='badge bg-{$badge_color}'>{$data['status_text']}</span>";
                    ?>
                        <tr class="<?php echo $baris_class; ?>">
                            <td><?php echo $no; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($data['nama']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($data['nama_instansi_asal']); ?></small>
                            </td>
                            <td><?php echo $status_badge; ?></td>
                            <td><?php echo $data['waktu'] ? date('H:i:s', strtotime($data['waktu'])) : '-'; ?></td>
                            <td><?php echo $data['waktu_pulang'] ? date('H:i:s', strtotime($data['waktu_pulang'])) : '-'; ?></td>
                            <td>
                                <?php
                                $nama_hari_inggris = date('l', strtotime($data['tanggal']));
                                echo MendapatkanHari($nama_hari_inggris) . ", " . date('d/m/Y', strtotime($data['tanggal']));
                                ?>
                            </td>
                            <td>
                                <?php
                                $keterangan_final_admin = $data['keterangan'];
                                if (in_array($data['status'], [2, 4, 5]) && !empty($data['alasan'])) {
                                    $keterangan_final_admin = $data['alasan'];
                                }
                                echo htmlspecialchars($keterangan_final_admin);
                                ?>
                            </td>
                            <td class="text-center">
                                <?php if ($data['status'] == 5): // Menunggu Persetujuan 
                                ?>
                                    <!-- Tombol Approve & Reject -->
                                    <button type="button" class="btn btn-success btn-sm tombol_persetujuan" data-idabsensi="<?php echo $data['id_absensi']; ?>" data-aksi="setujui" data-bs-toggle="tooltip" title="Setujui Izin"><i class="bi bi-check-lg"></i></button>
                                    <button type="button" class="btn btn-danger btn-sm tombol_persetujuan" data-idabsensi="<?php echo $data['id_absensi']; ?>" data-aksi="tolak" data-bs-toggle="tooltip" title="Tolak Izin"><i class="bi bi-x-lg"></i></button>
                                    <!-- Tombol lihat bukti izin jika ada -->
                                    <?php if (!empty($data['file_bukti'])): ?>
                                        <a href="apps/mahasiswa/bukti_izin/<?php echo htmlspecialchars($data['file_bukti']); ?>" target="_blank" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Lihat Bukti Izin"><i class="bi bi-paperclip"></i></a>
                                    <?php endif; ?>

                                <?php else: // Untuk status lainnya (Hadir, Izin Diterima, dll.) 
                                ?>
                                    <!-- Tombol Ubah Absensi Manual -->
                                    <button type="button" class="btn btn-secondary btn-sm absensi" id_absensi="<?php echo $data['id_absensi']; ?>" data-bs-toggle="tooltip" title="Ubah Absensi Manual"><i class="bi bi-pencil-square"></i></button>

                                    <!-- [BARU] Tombol Lihat Foto Absen jika status Hadir dan ada foto -->
                                    <?php if ($data['status'] == 1 && !empty($data['foto_absen'])): ?>
                                        <a href="apps/mahasiswa/foto_absen/<?php echo htmlspecialchars($data['foto_absen']); ?>" target="_blank" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Lihat Foto Absen">
                                            <i class="bi bi-camera-fill"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- [BARU] Tombol Lihat Bukti Izin (juga untuk status yg sudah final) -->
                                    <?php if (in_array($data['status'], [2, 4]) && !empty($data['file_bukti'])): ?>
                                        <a href="apps/mahasiswa/bukti_izin/<?php echo htmlspecialchars($data['file_bukti']); ?>" target="_blank" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Lihat Bukti Izin">
                                            <i class="bi bi-paperclip"></i>
                                        </a>
                                    <?php endif; ?>

                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="judul"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="tampil_data"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>

</div>

<!-- JAVASCRIPT KHUSUS UNTUK HALAMAN INI -->
<script>
    $(document).ready(function() {
        // ... (Semua script Anda yang lama: Tooltip, #tambah_absensi, .absensi, #tombol_cetak_laporan)
        // Inisialisasi Tooltip Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Script AJAX untuk halaman Data Absensi
        $('#tambah_absensi').on('click', function() {
            $.ajax({
                url: 'apps/data_absensi/tambah.php',
                method: 'post',
                success: function(data) {
                    $('#tampil_data').html(data);
                    document.getElementById("judul").innerHTML = 'Tambah Absensi Manual';
                }
            });
            var myModal = new bootstrap.Modal(document.getElementById('modal'));
            myModal.show();
        });

        $('.absensi').on('click', function() {
            var id_mahasiswa = $(this).attr("id_mahasiswa");
            var id_absensi = $(this).attr("id_absensi");
            $.ajax({
                url: 'apps/data_absensi/absensi.php',
                method: 'POST',
                data: {
                    id_mahasiswa: id_mahasiswa,
                    id_absensi: id_absensi
                },
                success: function(data) {
                    $('#tampil_data').html(data);
                    document.getElementById("judul").innerHTML = 'Ubah Data Absensi';
                }
            });
            var myModal = new bootstrap.Modal(document.getElementById('modal'));
            myModal.show();
        });

        $('.cetak').on('click', function() {
            var id_mahasiswa = $(this).attr("id_mahasiswa");
            $.ajax({
                url: 'apps/data_absensi/cetak.php',
                method: 'POST',
                data: {
                    id_mahasiswa: id_mahasiswa
                },
                success: function(data) {
                    $('#tampil_data').html(data);
                    document.getElementById("judul").innerHTML = 'Cetak Laporan Absensi';
                }
            });
            var myModal = new bootstrap.Modal(document.getElementById('modal'));
            myModal.show();
        });

        $('#tombol_cetak_laporan').on('click', function() {
            $.ajax({
                url: 'apps/data_absensi/cetak_form.php', // Arahkan ke file form baru
                method: 'post',
                success: function(data) {
                    $('#tampil_data').html(data);
                    document.getElementById("judul").innerHTML = 'Cetak Laporan Absensi';
                }
            });
            var myModal = new bootstrap.Modal(document.getElementById('modal'));
            myModal.show();
        });


        // [TAMBAHKAN BLOK BARU INI]
        // Aksi untuk tombol persetujuan (Approve/Reject)
        $('.tombol_persetujuan').on('click', function() {
            var id_absensi = $(this).data('idabsensi');
            var aksi = $(this).data('aksi');
            var konfirmasi_pesan = (aksi == 'setujui') ? 'Menyetujui' : 'Menolak';

            if (confirm(`Anda yakin ingin ${konfirmasi_pesan} pengajuan izin ini?`)) {
                $.ajax({
                    url: 'apps/data_absensi/proses_izin.php', // Arahkan ke file proses baru
                    method: 'POST',
                    data: {
                        id_absensi: id_absensi,
                        aksi: aksi
                    },
                    success: function(response) {
                        if (response.trim() === 'sukses') {
                            // Muat ulang halaman untuk melihat perubahan
                            window.location.href = 'index.php?page=data_absensi&approve=berhasil';
                        } else {
                            window.location.href = 'index.php?page=data_absensi&approve=gagal';
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
                    }
                });
            }
        });
    });
</script>