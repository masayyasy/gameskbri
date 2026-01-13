<?php
session_start();
include '../config/koneksi.php';
date_default_timezone_set('Europe/Istanbul'); // Waktu Turki (TRT)

// Wajib set header JSON agar fetch tidak error
header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login ulang.']);
    exit();
}

$id_user = $_SESSION['id_user'];
$tanggal_sekarang = date('Y-m-d');
$jam_sekarang = date('H:i:s');
$batas_siang = "12:00:00"; // Batas pemisah Masuk dan Pulang

// 1. Cek apakah user sudah punya data absen hari ini
$cek_absen = mysqli_query($conn, "SELECT * FROM absensi WHERE id_user = '$id_user' AND tanggal = '$tanggal_sekarang'");
$data = mysqli_fetch_assoc($cek_absen);

try {
    if (!$data) {
        // --- SKENARIO 1: BELUM ADA DATA HARI INI ---
        if ($jam_sekarang <= $batas_siang) {
            // Absen pagi hari -> Catat JAM MASUK
            $query = "INSERT INTO absensi (id_user, tanggal, jam_masuk, status) 
                      VALUES ('$id_user', '$tanggal_sekarang', '$jam_sekarang', 'Hadir')";
            $msg = "Berhasil Absen Masuk pada $jam_sekarang";
        } else {
            // Absen pertama kali tapi sudah sore/malam -> Catat JAM PULANG
            $query = "INSERT INTO absensi (id_user, tanggal, jam_masuk, jam_pulang, status) 
                      VALUES ('$id_user', '$tanggal_sekarang', '00:00:00', '$jam_sekarang', 'Hadir')";
            $msg = "Berhasil Absen Pulang pada $jam_sekarang (Tanpa absen pagi)";
        }
    } else {
    // --- SKENARIO 2: SUDAH ADA DATA HARI INI ---
    if ($jam_sekarang > $batas_siang) {
        // HAPUS pengecekan status lengkap agar user bisa absen pulang berkali-kali (lembur)
        // Sistem akan selalu mengupdate jam_pulang ke waktu terbaru setiap kali verifikasi wajah dilakukan
        
        $query = "UPDATE absensi SET jam_pulang = '$jam_sekarang' 
                  WHERE id_user = '$id_user' AND tanggal = '$tanggal_sekarang'";
                  
        $msg = "Berhasil Absen Pulang pada $jam_sekarang. Hati-hati di jalan!";
    } else {
        // Jika masih pagi (sebelum jam 12) dan sudah absen masuk
        echo json_encode(['success' => true, 'message' => 'Anda sudah melakukan absen masuk pagi ini.']);
        exit();
    }
}

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Sistem error: ' . $e->getMessage()]);
}