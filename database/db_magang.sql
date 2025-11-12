-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 12, 2025 at 08:26 AM
-- Server version: 8.0.43-cll-lve
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sipaten1_presensi`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_absensi`
--

CREATE TABLE `tbl_absensi` (
  `id_absensi` int NOT NULL,
  `id_mahasiswa` int DEFAULT NULL,
  `status` int DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `waktu_pulang` time DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `latitude_absen` decimal(10,8) DEFAULT NULL,
  `longitude_absen` decimal(11,8) DEFAULT NULL,
  `foto_absen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_absensi`
--

INSERT INTO `tbl_absensi` (`id_absensi`, `id_mahasiswa`, `status`, `waktu`, `waktu_pulang`, `tanggal`, `keterangan`, `latitude_absen`, `longitude_absen`, `foto_absen`) VALUES
(171, 32, 3, NULL, NULL, '2025-11-10', 'Tidak ada kabar (Otomatis)', NULL, NULL, NULL),
(172, 34, 3, NULL, NULL, '2025-11-10', 'Tidak ada kabar (Otomatis)', NULL, NULL, NULL),
(173, 35, 3, NULL, NULL, '2025-11-10', 'Tidak ada kabar (Otomatis)', NULL, NULL, NULL),
(174, 36, 3, NULL, NULL, '2025-11-10', 'Tidak ada kabar (Otomatis)', NULL, NULL, NULL),
(175, 37, 3, NULL, NULL, '2025-11-10', 'Tidak ada kabar (Otomatis)', NULL, NULL, NULL),
(176, 38, 3, NULL, NULL, '2025-11-10', 'Tidak ada kabar (Otomatis)', NULL, NULL, NULL),
(177, 37, 1, '07:09:08', NULL, '2025-11-11', NULL, -3.66103040, 103.76997380, 'absen_37_2025-11-11.jpg'),
(178, 38, 1, '07:22:44', NULL, '2025-11-11', NULL, -3.66099720, 103.76999690, 'absen_38_2025-11-11.jpg'),
(179, 34, 1, '07:23:33', '16:03:54', '2025-11-11', NULL, -3.66103740, 103.76995400, 'absen_34_2025-11-11.jpg'),
(180, 32, 1, '07:25:11', '16:04:22', '2025-11-11', NULL, -3.66103070, 103.76996590, 'absen_32_2025-11-11.jpg'),
(181, 36, 1, '07:27:49', NULL, '2025-11-11', NULL, -3.66102890, 103.76996920, 'absen_36_2025-11-11.jpg'),
(182, 35, 1, '07:29:29', '16:02:45', '2025-11-11', NULL, -3.66103110, 103.76998130, 'absen_35_2025-11-11.jpg'),
(183, 32, 1, '07:24:03', NULL, '2025-11-12', NULL, -3.66102100, 103.76996300, 'absen_32_2025-11-12.jpg'),
(184, 37, 1, '07:24:12', NULL, '2025-11-12', NULL, -3.66102530, 103.76997690, 'absen_37_2025-11-12.jpg'),
(185, 34, 1, '07:25:16', NULL, '2025-11-12', NULL, -3.66102310, 103.76996170, 'absen_34_2025-11-12.jpg'),
(186, 38, 1, '07:57:52', NULL, '2025-11-12', 'Terlambat 17 menit', -3.66112760, 103.77001950, 'absen_38_2025-11-12.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `id_admin` int NOT NULL,
  `kode_admin` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_admin`
--

INSERT INTO `tbl_admin` (`id_admin`, `kode_admin`, `nama`, `nip`, `email`) VALUES
(1, 'A001', 'Admin', '111', 'admin@gmail.com'),
(6, 'A002', 'rani', '000', 'raniw@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_alasan`
--

CREATE TABLE `tbl_alasan` (
  `id_alasan` int NOT NULL,
  `id_mahasiswa` int DEFAULT NULL,
  `alasan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `file_bukti` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kegiatan`
--

CREATE TABLE `tbl_kegiatan` (
  `id_kegiatan` int NOT NULL,
  `id_mahasiswa` int DEFAULT NULL,
  `kegiatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `waktu_awal` time DEFAULT NULL,
  `waktu_akhir` time DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_kegiatan`
--

INSERT INTO `tbl_kegiatan` (`id_kegiatan`, `id_mahasiswa`, `kegiatan`, `waktu_awal`, `waktu_akhir`, `tanggal`) VALUES
(162, 37, 'Apel dan menjelaskan aplikasi magang', '07:30:00', '08:00:00', '2025-11-11'),
(163, 34, 'Yel-yel', '07:30:00', '07:45:00', '2025-11-11'),
(164, 34, 'Menjaga stand BPS di Mall Pelyanan Publik', '08:00:00', '12:00:00', '2025-11-11'),
(165, 34, 'Melanjutkan membuat laporan PKL ', '13:00:00', '15:00:00', '2025-11-11'),
(166, 34, 'Menjaga PST', '15:00:00', '16:00:00', '2025-11-11'),
(167, 32, 'Yel-yel', '07:30:00', '07:45:00', '2025-11-11'),
(168, 32, 'Belajar cara pelayanan di mall pelayanan publik', '08:00:00', '08:30:00', '2025-11-11'),
(169, 32, 'Menjaga PST ', '09:00:00', '12:00:00', '2025-11-11'),
(170, 32, 'Membuat data arsip keuangan bulan oktober', '13:30:00', '16:00:00', '2025-11-11');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mahasiswa`
--

CREATE TABLE `tbl_mahasiswa` (
  `id_mahasiswa` int NOT NULL,
  `kode_mahasiswa` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_instansi_asal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jurusan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nim` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mulai_magang` date DEFAULT NULL,
  `akhir_magang` date DEFAULT NULL,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_telp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_telp_ortu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_telp_guru` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_mahasiswa`
--

INSERT INTO `tbl_mahasiswa` (`id_mahasiswa`, `kode_mahasiswa`, `nama`, `nama_instansi_asal`, `jurusan`, `nim`, `mulai_magang`, `akhir_magang`, `alamat`, `no_telp`, `no_telp_ortu`, `no_telp_guru`, `foto`) VALUES
(32, 'M001', 'Zahra Gustina', 'SMK N 1 Tanjung Agung', 'Teknik Komputer dan Jaringan', '4190', '2025-07-07', '2025-11-28', 'Jalan lintas semendo tebat semen, padang bindu, Kec. Panang Enim, Kab. Muara Enim', '082178160847', '6283879542859', '6281279598883', 'M001_1762780770.jpg'),
(34, 'M034', 'Fersha Ramadhan', 'SMK N 1 Tanjung Agung', 'Teknik Komputer dan Jaringan', '4166', '2025-07-07', '2025-11-28', 'Desa lesung batu, Kec. Tanjung agung, Kab. Muara Enim', '082185553298', '6282180148707', '6281279598883', 'M034_1762780984.jpg'),
(35, 'M035', 'M. Rizki Palendra', 'SMK N 1 Tanjung Agung', 'Teknik Komputer dan Jaringan', '4246', '2025-07-07', '2025-11-28', 'Desa lebak budi, Kec. Panang Enim, Kab. Muara Enim', '081235237732', '6283176809374', '6281271959506', 'M035_1762781241.jpg'),
(36, 'M036', 'Marcel', 'SMK N 1 Tanjung Agung', 'Teknik Komputer dan Jaringan', '4248', '2025-07-07', '2025-11-29', 'Desa indramayu, Kec. Panang Ennim, Kab. Muara Enim', '085609865710', '6285766948812', '6281271959506', 'M036_1762781435.jpg'),
(37, 'M037', 'Novendri', 'Politeknik Negeri Sriwijaya', 'Manajemen Informatika', '062330801638', '2025-08-13', '2025-11-14', 'Lahat', '082178879202', '6282177527231', '6283827302201', 'foto_default.png'),
(38, 'M038', 'Rani Amelia', 'Politeknik Negeri Sriwijaya', 'Manajemen Informatika', '062330801639', '2025-08-13', '2025-11-14', 'Bandar Lampung', '083827302201', '6282178879202', '', 'M038_1762781656.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_setting_absensi`
--

CREATE TABLE `tbl_setting_absensi` (
  `id_waktu` int DEFAULT NULL,
  `masuk_mulai` time DEFAULT NULL,
  `masuk_akhir` time DEFAULT NULL,
  `pulang_mulai` time DEFAULT NULL,
  `pulang_akhir` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_setting_absensi`
--

INSERT INTO `tbl_setting_absensi` (`id_waktu`, `masuk_mulai`, `masuk_akhir`, `pulang_mulai`, `pulang_akhir`) VALUES
(1, '07:05:00', '07:40:00', '15:50:00', '16:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_site`
--

CREATE TABLE `tbl_site` (
  `id_site` int DEFAULT NULL,
  `nama_instansi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pimpinan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pembimbing` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_telp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `template_wa` text COLLATE utf8mb4_general_ci,
  `latitude_kantor` decimal(10,8) DEFAULT NULL,
  `longitude_kantor` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_site`
--

INSERT INTO `tbl_site` (`id_site`, `nama_instansi`, `pimpinan`, `pembimbing`, `no_telp`, `alamat`, `website`, `logo`, `template_wa`, `latitude_kantor`, `longitude_kantor`) VALUES
(1, 'Badan Pusat Statistik Kab.Muara Enim', 'Pimpinan', 'Pembimbing', '911', 'Jalan bambang Utoyo', 'bps', 'logo.png', 'Yth. Orang Tua dari {nama_mahasiswa},\r\nAnanda telah berhasil melakukan presensi masuk pada:\r\nTanggal: {tanggal}\r\nJam: {waktu}\r\nKeterangan: {keterangan}\r\nTerima kasih.\r\n\r\nBPS Muara Enim Siap Terdepan', -3.66105410, 103.77006230);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id_user` int NOT NULL,
  `kode_pengguna` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `level` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id_user`, `kode_pengguna`, `username`, `password`, `level`) VALUES
(1, 'A001', 'admin', '$2a$12$4gCbHJ4zson8JtlIprtOOOCVK2LFG1vMzzFgxnAkmieetRW0fMtjG', 'Admin'),
(50, 'A002', 'admin-rani', '$2y$10$Ej9s1vZ7CAlleWSeQjcwruU.HpGYzfVSIqTUXRtvvmHR4.K6Ju2Q6', 'Admin'),
(51, 'M001', '4190', '$2y$10$y0aZP0ijlXS3Pd54EgT3n.G/B.e9FoWF.XEPDCMLoXIvtWTZ.gE1C', 'Mahasiswa'),
(53, 'M034', '4166', '$2y$10$AptVjL0GRfwmIdyxEf2liOd.ozSYsKd/ptugNFh1wXaM7f/lZ/xL2', 'Mahasiswa'),
(54, 'M035', '4246', '$2y$10$TAaAMcI0ZIjm2M9UXFhGNeldPI2jLDg47bDnIYVHnGiR3frRmtaE.', 'Mahasiswa'),
(55, 'M036', '4248', '$2y$10$Sh.JyvQHQo.CSBeur2Vsn.3gUhb/5uGLPA91fV837Ygn7Ebs.TNAO', 'Mahasiswa'),
(56, 'M037', '1638', '$2y$10$CenJQ4/e1D9r9rD83hyzHuw6Rd/s2O44XYzUzTQRbkh33FsKCAbjq', 'Mahasiswa'),
(57, 'M038', '1639', '$2y$10$A.QzJolwaWHNGrPJNsfA..NoJJUmnXvBsgqswqfC4vQUsPnX7yvpe', 'Mahasiswa');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  ADD PRIMARY KEY (`id_absensi`),
  ADD KEY `tbl_absensi_ibfk1_1` (`id_mahasiswa`);

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD KEY `kode_admin` (`kode_admin`);

--
-- Indexes for table `tbl_alasan`
--
ALTER TABLE `tbl_alasan`
  ADD PRIMARY KEY (`id_alasan`),
  ADD KEY `tbl_alasan_ibfk1_1` (`id_mahasiswa`);

--
-- Indexes for table `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  ADD PRIMARY KEY (`id_kegiatan`),
  ADD KEY `tbl_kegiatan_ibfk1_1` (`id_mahasiswa`);

--
-- Indexes for table `tbl_mahasiswa`
--
ALTER TABLE `tbl_mahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD KEY `kode_mahasiswa` (`kode_mahasiswa`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `kode_pengguna` (`kode_pengguna`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  MODIFY `id_absensi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_alasan`
--
ALTER TABLE `tbl_alasan`
  MODIFY `id_alasan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  MODIFY `id_kegiatan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT for table `tbl_mahasiswa`
--
ALTER TABLE `tbl_mahasiswa`
  MODIFY `id_mahasiswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  ADD CONSTRAINT `tbl_absensi_ibfk1_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbl_mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD CONSTRAINT `tbl_admin_ibfk_1` FOREIGN KEY (`kode_admin`) REFERENCES `tbl_user` (`kode_pengguna`);

--
-- Constraints for table `tbl_alasan`
--
ALTER TABLE `tbl_alasan`
  ADD CONSTRAINT `tbl_alasan_ibfk1_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbl_mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  ADD CONSTRAINT `tbl_kegiatan_ibfk1_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbl_mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_mahasiswa`
--
ALTER TABLE `tbl_mahasiswa`
  ADD CONSTRAINT `tbl_mahasiswa_ibfk_1` FOREIGN KEY (`kode_mahasiswa`) REFERENCES `tbl_user` (`kode_pengguna`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
