<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Beranda</li>
    </ol>
</div>

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
            
            </div>
        </div>
    </div>
</div>