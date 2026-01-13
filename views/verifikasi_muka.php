<?php
session_start();
include '../config/koneksi.php';
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}
$nama_user = $_SESSION['nama_lengkap'] ?? $_SESSION['username'];
$id_user = $_SESSION['id_user'];

// Ambil data wajah dari database untuk dicocokkan nanti
$query_wajah = mysqli_query($conn, "SELECT face_data FROM users WHERE id_user = '$id_user'");
$data_wajah = mysqli_fetch_assoc($query_wajah);
$face_data_db = $data_wajah['face_data'] ?? 'null';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="../assets/js/face-api.min.js"></script>
    <title>Verifikasi Wajah - AnkaraOne</title>
    <style>
        body {
            background-color: #fcfcfc;
            font-family: 'Segoe UI', sans-serif;
        }

        .main-container {
            padding: 80px 20px;
        }

        #video-container {
            position: relative;
            width: 400px;
            height: 300px;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            border: 4px solid #333;
            margin: 0 auto;
        }

        video,
        canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .status-box {
            width: 400px;
            margin: 15px auto;
            padding: 15px;
            border-radius: 10px;
            background: #fff;
            border: 1px solid #eee;
        }

        .step-active {
            color: #198754 !important;
            font-weight: bold;
            opacity: 1 !important;
            border-color: #198754 !important;
            background-color: #e8f5e9;
        }

        .step-item {
            opacity: 0.3;
            transition: 0.3s;
        }

        .reg-guide {
            font-size: 0.8rem;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container main-container">
        <div class="text-center mb-4">
            <h4 class="fw-bold">Absensi & Registrasi Wajah</h4>
            <p class="text-muted small">Posisikan wajah di tengah kamera dan ikuti panduan sistem.</p>
        </div>

        <div id="video-container">
            <video id="video" width="400" height="300" autoplay muted></video>
        </div>

        <div class="status-box shadow-sm text-center">
            <div class="mb-2"><span class="badge bg-primary" id="status-label">Memuat Model...</span></div>
            <div class="d-flex justify-content-around">
                <div id="step-blink" class="step-item"><i class="bi bi-eye-fill d-block fs-4"></i>Kedip</div>
                <div id="step-smile" class="step-item"><i class="bi bi-emoji-smile-fill d-block fs-4"></i>Senyum</div>
                <div id="step-done" class="step-item"><i class="bi bi-person-check-fill d-block fs-4"></i>Cocok</div>
            </div>
        </div>

        <div class="card mx-auto shadow-sm border-warning" style="max-width: 400px;">
            <div class="card-header bg-warning fw-bold text-center py-2">PENDAFTARAN WAJAH BARU</div>
            <div class="card-body text-center">
                <p id="reg-instruction" class="reg-guide mb-3 text-muted">Klik tombol di bawah untuk mulai merekam wajah
                </p>

                <div class="d-flex justify-content-between text-center mb-3">
                    <div id="reg-up" class="step-item px-3 py-1 border rounded small">&uarr; Atas</div>
                    <div id="reg-down" class="step-item px-3 py-1 border rounded small">&darr; Bawah</div>
                    <div id="reg-left" class="step-item px-3 py-1 border rounded small">&larr; Kiri</div>
                    <div id="reg-right" class="step-item px-3 py-1 border rounded small">&rarr; Kanan</div>
                </div>

                <button id="btn-reg" class="btn btn-warning btn-sm w-100 fw-bold"
                    onclick="startGuidedRegistration()">MULAI PENDAFTARAN</button>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        let blinkDetected = false;
        let smileDetected = false;
        let matchDetected = false;
        let absenSent = false;
        let modeRegistrasi = false;
        let currentStep = 0;
        let regDescriptors = [];
        let faceMatcher = null;

        // Ambil data wajah dari PHP
        const rawFaceData = <?php echo json_encode($face_data_db); ?>;

        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('../assets/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('../assets/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('../assets/models'),
            faceapi.nets.faceExpressionNet.loadFromUri('../assets/models')
        ]).then(initSystem);

        async function initSystem() {
            // Inisialisasi Face Matcher jika data wajah ada di DB
            if (rawFaceData !== 'null' && rawFaceData !== null) {
                const savedDescriptors = JSON.parse(rawFaceData).map(d => new Float32Array(Object.values(d)));
                faceMatcher = new faceapi.FaceMatcher(new faceapi.LabeledFaceDescriptors("User", savedDescriptors), 0.5);
            }
            startCamera();
        }

        function startCamera() {
            navigator.mediaDevices.getUserMedia({ video: {} })
                .then(stream => {
                    video.srcObject = stream;
                    document.getElementById('status-label').innerText = "Kamera Aktif";
                })
                .catch(err => alert("Gagal akses kamera: " + err));
        }

        video.addEventListener('play', () => {
            const canvas = faceapi.createCanvasFromMedia(video);
            document.getElementById('video-container').append(canvas);
            const displaySize = { width: 400, height: 300 };
            faceapi.matchDimensions(canvas, displaySize);

            setInterval(async () => {
                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceExpressions()
                    .withFaceDescriptors();

                if (detections.length > 0) {
                    const landmark = detections[0].landmarks;
                    const nose = landmark.getNose()[0];
                    const descriptor = detections[0].descriptor;

                    // --- A. MODE ABSENSI ---
                    if (!modeRegistrasi) {
                        // 1. Deteksi Senyum
                        if (detections[0].expressions.happy > 0.8) {
                            smileDetected = true;
                            document.getElementById('step-smile').classList.add('step-active');
                        }

                        // 2. Deteksi Kedip
                        const eye = landmark.getLeftEye();
                        const ear = (Math.abs(eye[1].y - eye[5].y) + Math.abs(eye[2].y - eye[4].y)) / (2 * Math.abs(eye[0].x - eye[3].x));
                        if (ear < 0.25) {
                            blinkDetected = true;
                            document.getElementById('step-blink').classList.add('step-active');
                        }

                        // 3. Deteksi Kecocokan Wajah
                        if (faceMatcher) {
                            const match = faceMatcher.findBestMatch(descriptor);
                            if (match.label !== 'unknown') {
                                matchDetected = true;
                                document.getElementById('step-done').classList.add('step-active');
                            }
                        } else {
                            document.getElementById('status-label').innerText = "Wajah Belum Terdaftar";
                            document.getElementById('status-label').className = "badge bg-danger";
                        }

                        // 4. KIRIM ABSEN OTOMATIS
                        if (smileDetected && blinkDetected && matchDetected && !absenSent) {
                            absenSent = true;
                            sendAbsensi();
                        }
                    }

                    // --- B. MODE REGISTRASI (GUIDED) ---
                    else {
                        let stepSuccess = false;
                        if (currentStep === 1 && nose.y < 110) stepSuccess = true; // UP
                        else if (currentStep === 2 && nose.y > 180) stepSuccess = true; // DOWN
                        else if (currentStep === 3 && nose.x < 170) stepSuccess = true; // LEFT
                        else if (currentStep === 4 && nose.x > 230) stepSuccess = true; // RIGHT

                        if (stepSuccess) {
                            const stepId = ["", "reg-up", "reg-down", "reg-left", "reg-right"];
                            document.getElementById(stepId[currentStep]).classList.add('step-active');
                            regDescriptors.push(Array.from(descriptor));
                            currentStep++;
                            if (currentStep > 4) finishRegistration();
                            else updateInstruction();
                        }
                    }
                }
            }, 200);
        });

        function sendAbsensi() {
            document.getElementById('status-label').innerText = "Mencatat Kehadiran...";

            fetch('proses_absen.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Jika berhasil absen baru atau sudah absen sebelumnya
                        alert(data.message);
                        window.location.href = 'absensi.php';
                    } else {
                        // Jika ada error teknis
                        alert("Gagal: " + data.message);
                        absenSent = false;
                    }
                })
                .catch(err => {
                    console.error("Error:", err);
                    alert("Terjadi gangguan koneksi ke server.");
                    absenSent = false;
                });
        }

        function startGuidedRegistration() {
            modeRegistrasi = true;
            currentStep = 1;
            regDescriptors = [];
            document.getElementById('btn-reg').disabled = true;
            updateInstruction();
        }

        function updateInstruction() {
            const text = ["", "Gerakkan kepala ke ATAS", "Gerakkan kepala ke BAWAH", "Tolehkan kepala ke KIRI", "Tolehkan kepala ke KANAN"];
            document.getElementById('reg-instruction').innerText = text[currentStep];
            document.getElementById('reg-instruction').className = "reg-guide mb-3 text-primary animate-pulse";
        }

        function finishRegistration() {
            modeRegistrasi = false;
            document.getElementById('reg-instruction').innerText = "Gerakan Lengkap! Menyimpan...";
            document.getElementById('btn-reg').innerText = "SIMPAN DATA SEKARANG";
            document.getElementById('btn-reg').disabled = false;
            document.getElementById('btn-reg').className = "btn btn-success btn-sm w-100 fw-bold";
            document.getElementById('btn-reg').onclick = saveToDatabase;
        }

        function saveToDatabase() {
            const payload = { faceData: JSON.stringify(regDescriptors) };
            fetch('simpan_wajah.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Data wajah berhasil didaftarkan!");
                        location.reload();
                    } else {
                        alert("Gagal: " + data.error);
                    }
                });
        }
    </script>
</body>

</html>