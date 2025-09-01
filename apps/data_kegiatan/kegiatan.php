<?php
    if ($_SESSION["level"] != 'Mahasiswa') {
        echo "<div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
        exit;
    }
    include 'config/function.php'; // Sertakan fungsi kustom
    $id_mahasiswa = $_SESSION['id_mahasiswa'];
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">Kegiatan Harian</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Laporan Kegiatan Harian Anda</h5>
        <div>
            <button type="button" class="btn btn-primary" id="tombol_kegiatan" data-idmahasiswa="<?php echo $id_mahasiswa; ?>">
                <i class="bi bi-plus-lg me-1"></i> Tambah Kegiatan
            </button>
            <button type="button" class="btn btn-secondary" id="cetak_kegiatan" data-idmahasiswa="<?php echo $id_mahasiswa; ?>">
                <i class="bi bi-printer-fill me-1"></i> Cetak
            </button>
        </div>
    </div>
    <div class="card-body">
        
        <?php
            // Notifikasi (jika diperlukan)
            if (isset($_GET['tambah']) && $_GET['tambah'] == 'berhasil') {
                echo "<div class='alert alert-success'><strong>Berhasil!</strong> Kegiatan harian telah ditambahkan.</div>";
            }
        ?>

        <div class="p-3 mb-4 rounded" style="background-color: #f8f9fa;">
            <form action="index.php" method="GET">
                <input type="hidden" name="page" value="kegiatan"/>
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="tanggal_awal" class="form-control" value="<?php echo isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : ''; ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="tanggal_akhir" class="form-control" value="<?php echo isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info w-100"><i class="bi bi-search"></i> Cari</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" style="width: 5%;">No</th>
                        <th class="text-center" style="width: 20%;">Hari, Tanggal</th>
                        <th class="text-center" style="width: 20%;">Jam</th>
                        <th style="width: 55%;">Uraian Kegiatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Memanggil fungsi dari function.php
                        if (isset($_GET['tanggal_awal']) && isset($_GET['tanggal_akhir']) && !empty($_GET['tanggal_awal'])) {
                            $tanggal_awal = $_GET["tanggal_awal"];
                            $tanggal_akhir = $_GET["tanggal_akhir"];
                            // PENTING: Kita akan asumsikan fungsi MencarikanKegiatan sudah diamankan
                            $sql = MencarikanKegiatan($id_mahasiswa, $tanggal_awal, $tanggal_akhir);
                        } else { 
                            $sql = MenampilkanKegiatan($id_mahasiswa);
                        }
                        
                        $hasil = mysqli_query($kon, $sql);
                        $no = 0;
                        while ($data = mysqli_fetch_array($hasil)):
                        $no++;
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $no; ?></td>
                        <td>
                            <?php
                                $nama_hari_inggris = date('l', strtotime($data['tanggal']));
                                echo MendapatkanHari($nama_hari_inggris) . ", " . date('d F Y', strtotime($data['tanggal']));
                            ?>
                        </td>
                        <td class="text-center">
                            <?php 
                                echo WaktuKegiatan($data['kegiatan']);
                            ?>
                        </td> 
                        <td>
                            <?php
                                echo BarisKegiatan($data['kegiatan']);
                            ?>
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
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var modal = new bootstrap.Modal(document.getElementById('modal'));

        $('#tombol_kegiatan').on('click', function(){
            var id_mahasiswa = $(this).data("idmahasiswa");
            $.ajax({
                url: 'apps/pengguna/mulai_kegiatan.php', // File ini mungkin perlu dimodernisasi form-nya
                method: 'POST',
                data: { id_mahasiswa: id_mahasiswa },
                success: function(data) {
                    $('#tampil_data').html(data);  
                    $('#judul').html('Tambah Kegiatan Harian');
                    modal.show();
                }
            });
        });

        $('#cetak_kegiatan').on('click', function(){
            var id_mahasiswa = $(this).data("idmahasiswa");
            $.ajax({
                url: 'apps/data_kegiatan/cetak.php', // File ini mungkin perlu dimodernisasi
                method: 'POST',
                data: { id_mahasiswa: id_mahasiswa },
                success: function(data) {
                    $('#tampil_data').html(data);  
                    $('#judul').html('Cetak Laporan Kegiatan');
                    modal.show();
                }
            });
        });
    });
</script>