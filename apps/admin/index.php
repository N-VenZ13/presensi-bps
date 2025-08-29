<?php 
  if ($_SESSION["level"] != 'Admin') {
    echo"<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
  }
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=beranda">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Administrator</li>
    </ol>
</nav>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Administrator</h5>
        <button type="button" class="btn btn-primary" id="tombol_tambah">
            <i class="bi bi-plus-lg me-1"></i> Tambah Administrator
        </button>
    </div>
    <div class="card-body">

        <?php
            function showAlert($type, $message) {
                $icon = ($type == 'success') ? 'check-circle-fill' : 'exclamation-triangle-fill';
                echo "<div class='alert alert-{$type} d-flex align-items-center' role='alert'><i class='bi bi-{$icon} me-2'></i><div>{$message}</div></div>";
            }
            if (isset($_GET['add'])) {
                if ($_GET['add']=='berhasil') showAlert('success', '<strong>Berhasil!</strong> Administrator baru telah ditambahkan.');
                else showAlert('danger', '<strong>Gagal!</strong> Administrator gagal ditambahkan.');
            }
            if (isset($_GET['edit'])) {
                if ($_GET['edit']=='berhasil') showAlert('success', '<strong>Berhasil!</strong> Data administrator telah diupdate.');
                else showAlert('danger', '<strong>Gagal!</strong> Data administrator gagal diupdate.');
            }
            if (isset($_GET['pengguna'])) {
                if ($_GET['pengguna']=='berhasil') showAlert('success', '<strong>Berhasil!</strong> Pengaturan pengguna telah disimpan.');
                else showAlert('danger', '<strong>Gagal!</strong> Pengaturan pengguna gagal disimpan.');
            }
            if (isset($_GET['hapus'])) {
                if ($_GET['hapus']=='berhasil') showAlert('success', '<strong>Berhasil!</strong> Administrator telah dihapus.');
                else showAlert('danger', '<strong>Gagal!</strong> Administrator gagal dihapus.');
            }
        ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $sql = "SELECT * FROM tbl_admin ORDER BY nama ASC";
                    $hasil = mysqli_query($kon, $sql);
                    $no = 0;
                    while ($data = mysqli_fetch_array($hasil)):
                    $no++;
                ?>
                <tr>
                    <td><?php echo $no; ?></td>
                    <td><?php echo htmlspecialchars($data['nip']); ?></td>
                    <td><?php echo htmlspecialchars($data['nama']); ?></td>
                    <td><?php echo htmlspecialchars($data['email']); ?></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-primary btn-sm tombol_setting_pengguna" kode_admin="<?php echo $data['kode_admin'];?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Setting Pengguna">
                            <i class="bi bi-person-gear"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm tombol_edit" id_admin="<?php echo $data['id_admin'];?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Data">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <a href="apps/admin/hapus.php?id_admin=<?php echo $data['id_admin']; ?>&kode_admin=<?php echo $data['kode_admin']; ?>" class="btn btn-danger btn-sm btn-hapus-admin" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Data">
                            <i class="bi bi-trash3-fill"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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

<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) });

    $('#tombol_tambah').on('click',function(){
        $.ajax({
            url: 'apps/admin/tambah.php',
            method: 'post',
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Tambah Administrator';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });

    $('.tombol_setting_pengguna').on('click',function(){
        var kode_admin = $(this).attr("kode_admin");
        $.ajax({
            url: 'apps/admin/pengguna.php',
            method: 'post',
            data: {kode_admin:kode_admin},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Setting Pengguna';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });

    $('.tombol_edit').on('click',function(){
        var id_admin = $(this).attr("id_admin");
        $.ajax({
            url: 'apps/admin/edit.php',
            method: 'post',
            data: {id_admin:id_admin},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Edit Administrator';
            }
        });
        var myModal = new bootstrap.Modal(document.getElementById('modal'));
        myModal.show();
    });

   $('.btn-hapus-admin').on('click',function(){
        return confirm("Apakah Anda yakin ingin menghapus administrator ini?");
    });
</script>