<?php
session_start();
include '../config/koneksi.php';
date_default_timezone_set('Europe/Istanbul'); // Set Waktu Turki

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dari sesi login
    $id_user      = $_SESSION['id_user'];
    
    // 2. Ambil data dari form input & bersihkan input teks
    $nama_request = mysqli_real_escape_string($conn, $_POST['nama_request']);
    $deadline     = $_POST['deadline'];
    $tgl_input    = $_POST['tgl_input'];
    $keterangan   = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // 3. Tetapkan nilai default (untuk diisi Kanselerai nanti di tabel log)
    $lokasi    = 'Lainnya';
    $progress  = 'Pending';
    $pic       = 'Gilang';
    $urgency   = 'Medium';

    // 4. Query INSERT lengkap ke tabel log_permintaan
    $query = "INSERT INTO log_permintaan 
              (id_user, nama_request, deadline, tgl_input, lokasi_gedung, progress, pic, urgency_level, keterangan) 
              VALUES 
              ('$id_user', '$nama_request', '$deadline', '$tgl_input', '$lokasi', '$progress', '$pic', '$urgency', '$keterangan')";

    // 5. Eksekusi simpan ke database permanen
    if (mysqli_query($conn, $query)) {
        // Berhasil simpan, kembalikan ke halaman absensi
        echo "<script>alert('Permintaan berhasil dikirim!'); window.location='absensi.php';</script>";
    } else {
        // Tampilkan pesan error teknis jika gagal
        echo "Gagal menyimpan data ke database: " . mysqli_error($conn);
    }
} else {
    // Jika diakses tanpa method POST, arahkan ke form
    header("Location: form_permintaan.php");
    exit();
}
?>