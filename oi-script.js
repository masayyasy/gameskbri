// --- DOM Elements (shared & page-specific) ---
// Elemen untuk home.html
const propertiForm = document.getElementById("propertiForm");
const propertiTableBody = document.getElementById("propertiTableBody");
const propertiDataTable = document.getElementById('propertiDataTable');
const koordinatInput = document.getElementById("koordinat");
const mapElement = document.getElementById("map");
const gradeSelect = document.getElementById("grade");
const referenceTable = document.getElementById('dataReferensiTable');
const referenceInputs = referenceTable ? referenceTable.querySelectorAll('input') : [];
const exportHomeTableBtn = document.getElementById('exportHomeTableBtn');
const exportTable2Btn = document.getElementById('exportTable2Btn'); // DIUBAH/DITAMBAH
const exportTable3Btn = document.getElementById('exportTable3Btn');
const exportAnalisisXLSXBtn = document.getElementById("exportAnalisisXLSX");

const nilaiDBKBInput = document.getElementById("nilaiDBKB");
const noResultsText = document.getElementById('noResultsText');

// Elemen untuk formulir3.html
const gradeInputJudul = document.getElementById("gradeInputJudul");
const analisisTableBody3 = document.getElementById("analisisTableBody3");
const analisisTable3 = document.getElementById("analisisDataTable3");

// Elemen untuk analisis.html (Formulir ZNT)
const alamatObjekInput = document.getElementById("alamatObjekInput");
const alamatDesaInput = document.getElementById("alamatDesaInput");
const kodeZNTInput = document.getElementById("kodeZNTInput");
const gradeInputDropdown = document.getElementById("gradeInputDropdown");


// --- Data Storage (shared) ---
let propertis = JSON.parse(localStorage.getItem('propertiData')) || [];
let dataReferensi = JSON.parse(localStorage.getItem('dataReferensi')) || {};

// URL Web App Google Apps Script
const GOOGLE_APPS_SCRIPT_URL = "https://script.google.com/macros/s/AKfycbz-9B6IgyHtjeiG_0VqoAeaCXkzVJeKP5ex05QgHpUhzMUEC8QZ4dWzi1DsoAyKuxbI/exec"; // ganti dengan URL Web App kamu

// Fungsi untuk kirim data ke Google Sheets (digunakan untuk TAMBAH data baru)
function syncToGoogleSheets(data) {
    // ✅ Tambahkan property 'action: "insert"'
    fetch(GOOGLE_APPS_SCRIPT_URL, {
        method: "POST",
        mode: "no-cors",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ...data, action: "insert" }) // Data baru harus menyertakan action: "insert"
    })
        .then(res => res.text())
        .then(txt => {
            console.log("Respon server (Tambah):", txt);
            // ✅ KRUSIAL: Muat ulang data dari Sheets setelah operasi POST sukses
            loadFromGoogleSheets();
        })
        .catch(err => console.error("Gagal sync ke Google Sheets:", err));
}

// Fungsi untuk ambil data dari Google Sheets (DIREVISI)
function loadFromGoogleSheets() {
    fetch(GOOGLE_APPS_SCRIPT_URL)
        .then(res => res.json())
        .then(data => {
            // 1. Simpan data terbaru ke variabel global
            propertis = data;

            // 2. Simpan ke localStorage (untuk Form 2 & 3)
            localStorage.setItem('propertiData', JSON.stringify(data));

            // 3. ✅ KRUSIAL: Render ulang tabel dengan data baru
            renderPropertiTable();
        })
        .catch(err => console.error("Gagal ambil data dari Google Sheets:", err));
}

// Objek untuk mendefinisikan grade tetap berdasarkan alamat
const alamatGrades = {
    "JL. LINTAS TIMUR": 9,
    "JL. POROS DESA": 8,
    "JL. KAPTEN TENDEAN": 7,
    "JL. SP-1 KAV": 8,
};

// Penanda untuk mode edit
let editModeId = null;
let map;
let marker;

// --- Utility Functions ---
function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 16 | 0,
            v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

// --- Map Functions (home.html specific) ---
function initializeMap() {
    if (mapElement && !map) {
        map = L.map('map').setView([-6.2088, 106.8456], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        map.on('click', onMapClick);
        map.invalidateSize();
    }
}

function onMapClick(e) {
    if (marker) map.removeLayer(marker);
    marker = L.marker(e.latlng).addTo(map);
    koordinatInput.value = `${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}`;
}

// --- Reference Table Functions (home.html specific) ---
function saveReferenceData() {
    const newDataReferensi = {};
    referenceInputs.forEach(input => {
        const id = input.dataset.id;
        let value = input.value;
        if (!isNaN(value) && value.trim() !== '') value = parseFloat(value);
        newDataReferensi[id] = value;
    });
    localStorage.setItem('dataReferensi', JSON.stringify(newDataReferensi));
    dataReferensi = newDataReferensi;
}

function loadReferenceData() {
    const storedData = JSON.parse(localStorage.getItem('dataReferensi'));
    if (storedData) {
        dataReferensi = storedData;
        referenceInputs.forEach(input => {
            const id = input.dataset.id;
            if (dataReferensi[id] !== undefined) input.value = dataReferensi[id];
        });
    }
}

// --- Form Handling (FINAL DAN BENAR) ---
if (propertiForm) {
    propertiForm.addEventListener("submit", function (event) {
        event.preventDefault();

        let newProperti = null;

        if (editModeId) {
            // 🔹 EDIT DATA
            const propertiIndex = propertis.findIndex(p => p.id === editModeId);
            if (propertiIndex > -1) {
                // (Mengumpulkan data baru dari form)
                newProperti = {
                    id: propertis[propertiIndex].id,
                    alamatObjekPajak: document.getElementById("alamatObjekPajak").value,
                    blokNop: document.getElementById("blokNop").value,
                    kodeZNT: document.getElementById("kodeZNT").value,
                    sumber: document.getElementById("sumber").value,
                    jenis: document.getElementById("jenis").value,
                    tanggal: document.getElementById("tanggal").value,
                    hargaTransaksi: parseFloat(document.getElementById("hargaTransaksi").value),
                    jenisPenggunaan: document.getElementById("jenisPenggunaan").value,
                    luas: parseFloat(document.getElementById("luas").value),
                    lebarSisiDepan: parseFloat(document.getElementById("lebarSisiDepan").value),
                    ketinggianDariJalan: parseFloat(document.getElementById("ketinggianDariJalan").value) || 0,
                    bentukTanah: document.getElementById("bentukTanah").value,
                    dataKepemilikan: document.getElementById("dataKepemilikan").value,
                    namaPemilik: document.getElementById("namaPemilik").value,
                    luasBangunan: parseFloat(document.getElementById("luasBangunan").value) || 0,
                    nilaiDBKB: parseFloat(nilaiDBKBInput.value) || 0,
                    grade: document.getElementById("grade").value,
                    koordinat: document.getElementById("koordinat").value,
                };

                // 🔹 Kirim update ke Google Sheets
                fetch(GOOGLE_APPS_SCRIPT_URL, {
                    method: "POST",
                    mode: "no-cors",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ ...newProperti, action: "update" })
                })
                    .then(res => res.text())
                    .then(txt => {
                        console.log("Respon update:", txt);
                        // ✅ KRUSIAL: Muat ulang data setelah UPDATE sukses
                        loadFromGoogleSheets();
                    })
                    .catch(err => console.error("Gagal update ke Google Sheets:", err));
            }
            editModeId = null;

        } else {
            // 🔹 TAMBAH DATA BARU
            newProperti = {
                id: generateUUID(),
                alamatObjekPajak: document.getElementById("alamatObjekPajak").value,
                blokNop: document.getElementById("blokNop").value,
                kodeZNT: document.getElementById("kodeZNT").value,
                sumber: document.getElementById("sumber").value,
                jenis: document.getElementById("jenis").value,
                tanggal: document.getElementById("tanggal").value,
                hargaTransaksi: parseFloat(document.getElementById("hargaTransaksi").value),
                jenisPenggunaan: document.getElementById("jenisPenggunaan").value,
                luas: parseFloat(document.getElementById("luas").value),
                lebarSisiDepan: parseFloat(document.getElementById("lebarSisiDepan").value),
                ketinggianDariJalan: parseFloat(document.getElementById("ketinggianDariJalan").value) || 0,
                bentukTanah: document.getElementById("bentukTanah").value,
                dataKepemilikan: document.getElementById("dataKepemilikan").value,
                namaPemilik: document.getElementById("namaPemilik").value,
                luasBangunan: parseFloat(document.getElementById("luasBangunan").value) || 0,
                nilaiDBKB: parseFloat(nilaiDBKBInput.value) || 0,
                grade: document.getElementById("grade").value,
                koordinat: document.getElementById("koordinat").value,
            };

            // 🔹 Kirim tambah ke Google Sheets (Ini sudah memanggil loadFromGoogleSheets di dalamnya)
            syncToGoogleSheets(newProperti);
        }

        // Bersihkan form (Ini tetap dipertahankan)
        propertiForm.reset();
        if (marker) { map.removeLayer(marker); marker = null; }
        koordinatInput.value = '';
    });
}

// --- Table Rendering (home.html specific) ---
function renderPropertiTable() {
    if (!propertiTableBody) return;

    propertiTableBody.innerHTML = '';
    if (propertis.length === 0) {
        propertiTableBody.innerHTML = '<tr><td colspan="19">Belum ada data properti yang ditambahkan.</td></tr>';
        return;
    }

    propertis.forEach((data, index) => {
        const row = propertiTableBody.insertRow();
        row.insertCell().textContent = index + 1;
        row.insertCell().textContent = data.alamatObjekPajak;
        row.insertCell().textContent = data.blokNop;
        row.insertCell().textContent = data.kodeZNT;
        row.insertCell().textContent = data.sumber;
        row.insertCell().textContent = data.jenis;
        row.insertCell().textContent = data.dataKepemilikan;
        row.insertCell().textContent = data.tanggal;
        row.insertCell().textContent = new Intl.NumberFormat('id-ID').format(data.hargaTransaksi);
        row.insertCell().textContent = data.jenisPenggunaan;
        row.insertCell().textContent = data.luas;
        row.insertCell().textContent = data.lebarSisiDepan;
        row.insertCell().textContent = data.ketinggianDariJalan;
        row.insertCell().textContent = data.bentukTanah;
        row.insertCell().textContent = data.namaPemilik;
        row.insertCell().textContent = data.luasBangunan;
        row.insertCell().textContent = new Intl.NumberFormat('id-ID').format(data.nilaiDBKB);
        row.insertCell().textContent = data.grade;
        row.insertCell().textContent = data.koordinat;

        const actionCell = row.insertCell();
        actionCell.classList.add('action-buttons');

        const editButton = document.createElement('button');
        editButton.textContent = 'Edit';
        editButton.classList.add('edit-btn');
        editButton.onclick = () => editProperti(data.id);
        actionCell.appendChild(editButton);

        const deleteButton = document.createElement('button');
        deleteButton.textContent = 'Delete';
        deleteButton.classList.add('delete-btn');
        deleteButton.onclick = () => deleteProperti(data.id);
        actionCell.appendChild(deleteButton);
    });
}

// --- Edit/Delete Functions (home.html specific) ---
function editProperti(id) {
    const propertiToEdit = propertis.find(p => p.id === id);
    if (propertiToEdit) {
        document.getElementById("alamatObjekPajak").value = propertiToEdit.alamatObjekPajak;
        document.getElementById("blokNop").value = propertiToEdit.blokNop;
        document.getElementById("kodeZNT").value = propertiToEdit.kodeZNT;
        document.getElementById("sumber").value = propertiToEdit.sumber;
        document.getElementById("jenis").value = propertiToEdit.jenis;
        document.getElementById("tanggal").value = propertiToEdit.tanggal;
        document.getElementById("hargaTransaksi").value = propertiToEdit.hargaTransaksi;
        document.getElementById("jenisPenggunaan").value = propertiToEdit.jenisPenggunaan;
        document.getElementById("luas").value = propertiToEdit.luas;
        document.getElementById("lebarSisiDepan").value = propertiToEdit.lebarSisiDepan;
        document.getElementById("ketinggianDariJalan").value = propertiToEdit.ketinggianDariJalan;
        document.getElementById("bentukTanah").value = propertiToEdit.bentukTanah;
        document.getElementById("dataKepemilikan").value = propertiToEdit.dataKepemilikan;
        document.getElementById("namaPemilik").value = propertiToEdit.namaPemilik;
        document.getElementById("luasBangunan").value = propertiToEdit.luasBangunan;
        nilaiDBKBInput.value = propertiToEdit.nilaiDBKB;
        document.getElementById("grade").value = propertiToEdit.grade;
        document.getElementById("koordinat").value = propertiToEdit.koordinat;
        editModeId = id;

        if (propertiToEdit.koordinat && map) {
            const [lat, lng] = propertiToEdit.koordinat.split(', ').map(Number);
            if (!isNaN(lat) && !isNaN(lng)) {
                map.setView([lat, lng], 16);
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lng]).addTo(map);
            }
        }
    }
}

// ... (Fungsi editProperti tetap sama)

function deleteProperti(id) {
    const modalMessage = "Apakah Anda yakin ingin menghapus data ini?";
    const modal = createModal(modalMessage, () => {
        // Hapus dari localStorage dan array lokal (sementara)
        propertis = propertis.filter(p => p.id !== id);
        localStorage.setItem('propertiData', JSON.stringify(propertis));
        renderPropertiTable(); // Render lokal untuk feedback cepat

        // Kirim permintaan hapus ke Google Sheets
        syncDeleteToGoogleSheets(id);

        closeModal(modal);
    });
}

// Fungsi untuk mengirim permintaan Hapus ke Apps Script
function syncDeleteToGoogleSheets(id) {
    // ⚠️ Pastikan ini adalah fungsi ini yang Anda gunakan, dan hapus duplikat lainnya!
    fetch(GOOGLE_APPS_SCRIPT_URL + "?action=delete&id=" + id, {
        method: "GET",
    })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            return res.text();
        })
        .then(txt => {
            console.log("Respon server Hapus:", txt);
            // ✅ Setelah sukses, muat ulang data dari Sheets untuk memastikan sinkronisasi penuh
            loadFromGoogleSheets();
        })
        .catch(err => {
            console.error("Gagal hapus dari Google Sheets:", err);
            // 🚨 Jika gagal, panggil ulang loadFromGoogleSheets() untuk menarik kembali data yang gagal dihapus dari Sheets
            loadFromGoogleSheets();
            createModal("Gagal menghapus data dari server. Silakan coba lagi.", () => { }); // Beri notifikasi ke user
        });
}

// --- Modal Kustom (untuk mengganti alert/confirm) ---
function createModal(message, onConfirm) {
    const modalContainer = document.createElement('div');
    modalContainer.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center';

    const modalContent = document.createElement('div');
    modalContent.className = 'bg-white p-6 rounded-lg shadow-lg max-w-sm mx-auto';
    modalContainer.appendChild(modalContent);

    const messageEl = document.createElement('p');
    messageEl.className = 'text-gray-800 text-lg mb-4';
    messageEl.textContent = message;
    modalContent.appendChild(messageEl);

    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'flex justify-end space-x-2';
    modalContent.appendChild(buttonContainer);

    const cancelButton = document.createElement('button');
    cancelButton.className = 'px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-300';
    cancelButton.textContent = 'Batal';
    cancelButton.onclick = () => closeModal(modalContainer);
    buttonContainer.appendChild(cancelButton);

    const confirmButton = document.createElement('button');
    confirmButton.className = 'px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-300';
    confirmButton.textContent = 'Hapus';
    confirmButton.onclick = onConfirm;
    buttonContainer.appendChild(confirmButton);

    document.body.appendChild(modalContainer);
    return modalContainer;
}

function closeModal(modal) {
    modal.remove();
}

// --- Dropdown Population ---
function populateGradeDropdown() {
    if (!gradeSelect) return;

    for (let i = 1; i <= 10; i++) {
        const option = document.createElement("option");
        option.value = `Grade ${i}`;
        option.textContent = `Grade ${i}`;
        gradeSelect.appendChild(option);
    }
}

// =========================================================================
// --- Formulir 2 Specific JavaScript ---
// =========================================================================
function renderAnalisisTable() {
    const analisisTableBody = document.getElementById("analisisTableBody");
    if (!analisisTableBody) return;

    analisisTableBody.innerHTML = '';
    const storedPropertis = JSON.parse(localStorage.getItem('propertiData')) || [];

    if (storedPropertis.length === 0) {
        analisisTableBody.innerHTML = '<tr><td colspan="15">Belum ada data properti yang ditambahkan.</td></tr>';
        return;
    }

    storedPropertis.forEach((data, index) => {
        const row = analisisTableBody.insertRow();

        row.insertCell().textContent = index + 1;
        row.insertCell().textContent = data.alamatObjekPajak;
        row.insertCell().textContent = data.blokNop;
        row.insertCell().textContent = data.kodeZNT
        row.insertCell().textContent = data.luas;
        row.insertCell().textContent = new Intl.NumberFormat('id-ID').format(data.hargaTransaksi);
        row.insertCell().textContent = data.jenis;
        row.insertCell().textContent = data.tanggal;

        const jenisAdjusted = calculateJenisAdjusted(data.jenis);
        row.insertCell().textContent = jenisAdjusted;

        const waktuAdjusted = calculateWaktuAdjusted(data.tanggal);
        row.insertCell().textContent = waktuAdjusted;

        const hargaWajar = calculateHargaWajar(
            data.hargaTransaksi,
            jenisAdjusted,
            waktuAdjusted
        );

        row.insertCell().textContent = new Intl.NumberFormat('id-ID').format(hargaWajar);

        row.insertCell().textContent = data.luasBangunan;

        row.insertCell().textContent = new Intl.NumberFormat('id-ID').format(data.nilaiDBKB);

        const selisihNilai = hargaWajar - data.nilaiDBKB;
        row.insertCell().textContent = new Intl.NumberFormat('id-ID').format(selisihNilai);

        const hargaWajarPerM2 = data.luas > 0 ? (hargaWajar - data.nilaiDBKB) / data.luas : 0;
        row.insertCell().textContent = new Intl.NumberFormat('id-ID').format(hargaWajarPerM2);
    });
}

// =========================================================================
// --- FUNGSI BARU: Sinkronisasi Formulir 2 ke Google Sheets (Sheet2) ---
// =========================================================================
function syncFormulir2ToGoogleSheets() {
    // Gunakan propertis yang sudah dimuat secara global
    const dataToSend = propertis.map(data => {
        const jenisAdjusted = calculateJenisAdjusted(data.jenis);
        const waktuAdjusted = calculateWaktuAdjusted(data.tanggal);
        const hargaWajar = calculateHargaWajar(parseFloat(data.hargaTransaksi), jenisAdjusted, waktuAdjusted);
        const selisihNilai = hargaWajar - (parseFloat(data.nilaiDBKB) || 0);
        const hargaWajarPerM2 = (data.luas > 0) ? selisihNilai / parseFloat(data.luas) : 0;

        return {
            sheet: "Formulir2", // KRUSIAL untuk Apps Script doPost
            alamat: data.alamatObjekPajak,
            nop: data.blokNop,
            kodeZNT: data.kodeZNT,
            luas: data.luas,
            harga: data.hargaTransaksi,
            jenisData: data.jenis,
            tanggal: data.tanggal,
            penyesuaianJenis: jenisAdjusted,
            penyesuaianWaktu: waktuAdjusted,
            nilaiWajarTransaksi: hargaWajar,
            luasBangunan: data.luasBangunan,
            dbkb: data.nilaiDBKB,
            nilaiWajarBumi: selisihNilai,
            nilaiWajarPerM2: hargaWajarPerM2
        };
    });

    // Kirim data satu per satu
    dataToSend.forEach(data => {
        fetch(GOOGLE_APPS_SCRIPT_URL, {
            method: "POST",
            mode: "no-cors",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        })
            .then(res => res.text())
            .then(txt => console.log("Respon server Formulir 2:", txt))
            .catch(err => console.error("Gagal sync Formulir 2 ke Google Sheets:", err));
    });
}

// =========================================================================
// --- Formulir 3 Specific JavaScript ---
// =========================================================================
function renderAnalisisTable3() {
    if (!analisisTableBody3) return;

    analisisTableBody3.innerHTML = '';
    const storedPropertis = JSON.parse(localStorage.getItem('propertiData')) || [];
    const targetGrade = parseInt(gradeInputJudul.value);

    if (storedPropertis.length === 0) {
        analisisTableBody3.innerHTML = '<tr><td colspan="11">Belum ada data properti yang ditambahkan.</td></tr>';
        return;
    }

    let totalHargaWajarPerM2 = 0;
    let validCount = 0;

    storedPropertis.forEach(data => {
        const jenisAdjusted = calculateJenisAdjusted(data.jenis);
        const waktuAdjusted = calculateWaktuAdjusted(data.tanggal);

        const hargaWajar = calculateHargaWajar(
            data.hargaTransaksi,
            jenisAdjusted,
            waktuAdjusted
        );

        const hargaWajarPerM2 = data.luas > 0 ? (hargaWajar - data.nilaiDBKB) / data.luas : 0;

        if (hargaWajarPerM2 > 0) {
            totalHargaWajarPerM2 += hargaWajarPerM2;
            validCount++;
        }
    });

    const rataRataHargaWajarM2 = validCount > 0 ? totalHargaWajarPerM2 / validCount : 0;

    storedPropertis.forEach((data, index) => {
        const row = analisisTableBody3.insertRow();

        const jenisAdjusted = calculateJenisAdjusted(data.jenis);
        const kepemilikanAdjusted = calculateDataKepemilikanAdjusted(data.dataKepemilikan);
        const bentukTanahAdjusted = calculateBentukTanahAdjusted(data.bentukTanah);
        const ketinggianValue = data.ketinggianDariJalan !== undefined && data.ketinggianDariJalan !== null ? data.ketinggianDariJalan : 0;

        const hargaWajar = calculateHargaWajar(
            data.hargaTransaksi,
            jenisAdjusted,
            calculateWaktuAdjusted(data.tanggal)
        );

        const ketinggianAdjusted = calculateKetinggianAdjusted(ketinggianValue, data.luas, hargaWajar);

        // Nilai selisih yang digunakan sebagai input untuk rumus Nilai Wajar Objek
        const selisihNilai = data.luas > 0 ? (hargaWajar - data.nilaiDBKB) : 0;

        const hargaWajarPerM2 = data.luas > 0 ? selisihNilai / data.luas : 0;

        const gradeNumber = data.grade ? parseInt(data.grade.replace('Grade ', '')) : 0;

        // Logika baru untuk Lokasi (bakal grade)
        let lokasiBakalGrade;
        if (gradeNumber === targetGrade) {
            lokasiBakalGrade = 0;
        } else if (gradeNumber < targetGrade) {
            lokasiBakalGrade = gradeNumber / targetGrade;
        } else {
            lokasiBakalGrade = targetGrade / gradeNumber;
        }

        const nilaiWajarObjek3 = calculateHargaWajarform3(
            selisihNilai,
            lokasiBakalGrade,
            bentukTanahAdjusted,
            ketinggianAdjusted,
            kepemilikanAdjusted
        );

        row.insertCell().textContent = index + 1;
        row.insertCell().textContent = data.alamatObjekPajak;
        row.insertCell().textContent = data.blokNop;
        row.insertCell().textContent = data.kodeZNT;
        row.insertCell().textContent = data.grade;
        row.insertCell().textContent = new Intl.NumberFormat('id-ID').format(hargaWajarPerM2);
        row.insertCell().textContent = lokasiBakalGrade.toFixed(2);
        row.insertCell().textContent = bentukTanahAdjusted;
        row.insertCell().textContent = new Intl.NumberFormat('id-ID').format(ketinggianAdjusted);
        row.insertCell().textContent = kepemilikanAdjusted;

        row.insertCell().textContent = new Intl.NumberFormat('id-ID').format(nilaiWajarObjek3);

        row.insertCell().textContent = new Intl.NumberFormat('id-ID').format(rataRataHargaWajarM2);
    });
}

// =========================================================================
// --- FUNGSI BARU: Sinkronisasi Analisis ZNT (Formulir 3) ke Google Sheets (Sheet3) ---
// =========================================================================
function syncAnalisisToGoogleSheets(alamatObjek, desa, znt, grade) {
    const data = {
        sheet: "Analisis", // KRUSIAL untuk Apps Script doPost
        alamatObjek: alamatObjek,
        desa: desa,
        znt: znt,
        grade: grade
    };

    fetch(GOOGLE_APPS_SCRIPT_URL, {
        method: "POST",
        mode: "no-cors",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
        .then(res => res.text())
        .then(txt => console.log("Respon server Analisis ZNT:", txt))
        .catch(err => console.error("Gagal sync Analisis ZNT ke Google Sheets:", err));
}

// === Analisis.html (Analisis ZNT) - REPLACE THIS BLOCK IN script.js ===
document.addEventListener('DOMContentLoaded', () => {

    // ================= HOME =================

    if (propertiForm) {
        populateGradeDropdown();
        initializeMap();
        loadReferenceData();
        loadFromGoogleSheets(); // Ambil data dari Sheets saat load
        referenceInputs.forEach(input => input.addEventListener('change', saveReferenceData));
        if (exportHomeTableBtn) {
            exportHomeTableBtn.addEventListener('click', () => {
                exportTableToCSV('propertiDataTable', 'data_properti_input.csv');
            });
        }
    }

    // --- FORMULIR 2 ---
    if (document.getElementById("analisisDataTable")) {
        dataReferensi = JSON.parse(localStorage.getItem('dataReferensi')) || {};
        propertis = JSON.parse(localStorage.getItem('propertiData')) || [];

        // 🔹 Render langsung dari localStorage dulu agar tampil instan
        renderAnalisisTable();

        // 🔹 Lalu ambil versi terbaru dari Google Sheets (jaga sinkronisasi)
        loadFromGoogleSheets();

        const exportTable2Btn = document.getElementById('exportTable2Btn'); // AMBIL TOMBOL EKSPOR
        if (exportTable2Btn) {
            exportTable2Btn.addEventListener('click', () => {
                // 1. Ekspor ke CSV
                exportTableToCSV('analisisDataTable', 'analisis_nilai_pasar_wajar.csv');
                // 2. Sinkronisasi ke Google Sheets (Sheet2)
                syncFormulir2ToGoogleSheets();
                alert("Data Formulir 2 berhasil diekspor ke CSV dan disinkronkan ke Sheet2 Google Drive.");
            });
        }
    }

     // ================= ANALISIS ZNT =================
    const analisisTable = document.getElementById("analisisDataTable5");

    // Identifikasi jika kita berada di halaman analisis.html
    if (analisisTable && document.getElementById("alamatObjekInput")) {
        const alamatObjekInput = document.getElementById("alamatObjekInput");
        const alamatDesaInput = document.getElementById("alamatDesaInput");
        const kodeZNTInput = document.getElementById("kodeZNTInput");
        const gradeInputDropdown = document.getElementById("gradeInputDropdown");
        const alamatSelects = [
            document.getElementById("alamat1Select"),
            document.getElementById("alamat2Select"),
            document.getElementById("alamat3Select"),
            document.getElementById("alamat4Select"),
            document.getElementById("alamat5Select")
        ];
        const showAnalysisBtn = document.getElementById("showAnalysisBtn");
        const analisisTableBody5 = document.getElementById("analisisTableBody5");
        const historyListEl = document.getElementById("historyList");
        const clearHistoryBtn = document.getElementById("clearHistoryBtn");

        // Load data Riwayat dari LocalStorage
        let historyAnalisis = JSON.parse(localStorage.getItem('historyAnalisisZNT')) || [];

        // Fungsi Render List Riwayat
        function renderHistoryList() {
            if (!historyListEl) return;
            historyListEl.innerHTML = "";

            if (historyAnalisis.length === 0) {
                historyListEl.innerHTML = '<p class="text-sm text-gray-400 text-center py-4 italic">Belum ada riwayat.</p>';
                return;
            }

            historyAnalisis.forEach((item, index) => {
                const itemDiv = document.createElement("div");
                itemDiv.className = "history-item p-3 border rounded-lg text-sm transition-all mb-2 shadow-sm bg-white";
                itemDiv.innerHTML = `
                    <div class="font-bold text-gray-700 truncate">${item.objek.alamat}</div>
                    <div class="text-[10px] text-gray-400 flex justify-between">
                        <span>ZNT: ${item.objek.znt || '-'} | Grade: ${item.objek.grade}</span>
                        <span>${item.timestamp}</span>
                    </div>
                `;
                itemDiv.onclick = () => loadFromHistory(index);
                historyListEl.appendChild(itemDiv);
            });
        }

        // Fungsi Load data lama ke Form
        function loadFromHistory(index) {
            const data = historyAnalisis[index];
            if (!data) return;

            // Isi input field
            alamatObjekInput.value = data.objek.alamat;
            alamatDesaInput.value = data.objek.desa;
            kodeZNTInput.value = data.objek.znt;
            gradeInputDropdown.value = data.objek.grade;

            // Isi dropdown pembanding
            data.comparators.forEach((id, i) => {
                if (alamatSelects[i]) alamatSelects[i].value = id;
            });

            // Jalankan analisis (tanpa simpan ulang ke riwayat untuk menghindari duplikat)
            runAnalysisLogic(false);

            // Tandai item aktif di sidebar
            document.querySelectorAll('.history-item').forEach((el, i) => {
                el.classList.toggle('active-history', i === index);
            });
        }

        // Logika Inti Analisis (dipisah agar bisa dipanggil saat load history)
        function runAnalysisLogic(isNewSave = true) {
            analisisTableBody5.innerHTML = "";

            const objekAlamat = alamatObjekInput.value.trim();
            const objekDesa = alamatDesaInput.value.trim();
            const objekKodeZNT = kodeZNTInput.value.trim();
            const objekGrade = parseInt(gradeInputDropdown.value) || 0;

            if (!objekAlamat || objekGrade <= 0) {
                alert("Harap lengkapi Alamat dan Grade Lokasi.");
                return;
            }

            const selectedIds = alamatSelects.map(s => s.value).filter(id => id);
            if (selectedIds.length === 0) {
                alert("Harap pilih minimal satu pembanding.");
                return;
            }

            // Simpan ke Riwayat jika ini adalah klik tombol "Nilai" baru
            if (isNewSave) {
                const newHistory = {
                    timestamp: new Date().toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }),
                    objek: { alamat: objekAlamat, desa: objekDesa, znt: objekKodeZNT, grade: objekGrade },
                    comparators: selectedIds
                };
                historyAnalisis.unshift(newHistory); // Tambah ke paling atas
                if (historyAnalisis.length > 20) historyAnalisis.pop(); // Batasi 20 riwayat
                localStorage.setItem('historyAnalisisZNT', JSON.stringify(historyAnalisis));
                renderHistoryList();
            }

            // --- Bagian Render Tabel (sama seperti logika Anda sebelumnya) ---
            let indexTabel = 0;
            let totalNilaiWajar = 0;
            let counter = 0;

            const dataProperti = JSON.parse(localStorage.getItem('propertiData')) || [];
            const dataReferensiLocal = JSON.parse(localStorage.getItem('dataReferensi')) || {};

            selectedIds.forEach(id => {
                const item = dataProperti.find(p => String(p.id) === String(id));
                if (!item) return;

                const pembandingGrade = parseGrade(item.grade);
                const lokasiBakalGrade = (objekGrade - pembandingGrade) * 0.1;
                const bentukTanahAdj = (String(item.bentukTanah).toLowerCase().includes('normal')) ? 0 : (parseFloat(dataReferensiLocal.penyesuaian_bentuk_tanah) || 0);

                let ketinggianVal = parseFloat(item.ketinggianDariJalan) || 0;
                let penyesuaianTinggiRef = parseFloat(dataReferensiLocal.penyesuaian_ketinggian) || 0;
                const ketinggianAdj = Number((ketinggianVal * penyesuaianTinggiRef).toFixed(4));

                const kepemilikanAdj = (String(item.dataKepemilikan).toUpperCase() === 'SHM') ? 0 : (parseFloat(dataReferensiLocal.penyesuaian_sertifikat) || 0);

                const nwbM2 = computeNilaiWajarBumiPerM2(item);
                const nilaiWajarObjek = nwbM2 * (lokasiBakalGrade + bentukTanahAdj + ketinggianAdj + kepemilikanAdj);

                totalNilaiWajar += nilaiWajarObjek;
                counter++;

                const row = analisisTableBody5.insertRow();
                row.innerHTML = `
                    <td class="px-4 py-2 border text-center">${++indexTabel}</td>
                    <td class="px-4 py-2 border">${item.alamatObjekPajak}</td>
                    <td class="px-4 py-2 border">${item.blokNop}</td>
                    <td class="px-4 py-2 border text-center">${item.kodeZNT}</td>
                    <td class="px-4 py-2 border text-center">${item.grade}</td>
                    <td class="px-4 py-2 border text-right">${new Intl.NumberFormat('id-ID').format(nwbM2)}</td>
                    <td class="px-4 py-2 border text-center">${lokasiBakalGrade.toFixed(2)}</td>
                    <td class="px-4 py-2 border text-center">${bentukTanahAdj.toFixed(2)}</td>
                    <td class="px-4 py-2 border text-center">${ketinggianAdj.toFixed(4)}</td>
                    <td class="px-4 py-2 border text-center">${kepemilikanAdj.toFixed(2)}</td>
                    <td class="px-4 py-2 border text-right font-bold">${new Intl.NumberFormat('id-ID').format(nilaiWajarObjek)}</td>
                    <td class="px-4 py-2 border text-right bg-blue-50">${new Intl.NumberFormat('id-ID').format(totalNilaiWajar / counter)}</td>
                `;
            });

            // Sinkronisasi ke Google Sheets (jika ada fungsi ini)
            if (typeof syncAnalisisToGoogleSheets === 'function') {
                syncAnalisisToGoogleSheets(objekAlamat, objekDesa, objekKodeZNT, objekGrade);
            }
        }

        // Event Listener Tombol Nilai
        showAnalysisBtn.onclick = () => runAnalysisLogic(true);

        // Hapus Riwayat
        if (clearHistoryBtn) {
            clearHistoryBtn.onclick = () => {
                if (confirm("Hapus semua riwayat analisis?")) {
                    historyAnalisis = [];
                    localStorage.removeItem('historyAnalisisZNT');
                    renderHistoryList();
                    analisisTableBody5.innerHTML = '<tr><td colspan="12" class="px-6 py-10 text-center text-gray-400 italic">Riwayat dihapus.</td></tr>';
                }
            };
        }

        // Helper functions (Pastikan fungsi ini ada/diambil dari kode lama Anda)
        function parseGrade(val) {
            const d = String(val).match(/\d+/);
            return d ? parseInt(d[0]) : 0;
        }

        function computeNilaiWajarBumiPerM2(item) {
            const jAdj = typeof calculateJenisAdjusted === 'function' ? calculateJenisAdjusted(item.jenis) : 1;
            const wAdj = typeof calculateWaktuAdjusted === 'function' ? calculateWaktuAdjusted(item.tanggal) : 1;
            const hWajar = typeof calculateHargaWajar === 'function' ? calculateHargaWajar(item.hargaTransaksi, jAdj, wAdj) : item.hargaTransaksi;
            return item.luas > 0 ? (hWajar - (item.nilaiDBKB || 0)) / item.luas : 0;
        }

        // Inisialisasi tampilan Riwayat saat pertama kali buka
        renderHistoryList();

        // Populate dropdown alamat pembanding (diambil dari kode asli Anda)
        const dataPropertiCache = JSON.parse(localStorage.getItem('propertiData')) || [];
        alamatSelects.forEach(select => {
            select.innerHTML = '<option value="">-- Pilih Alamat Pembanding --</option>';
            dataPropertiCache.forEach(p => {
                const opt = document.createElement("option");
                opt.value = p.id;
                opt.textContent = p.alamatObjekPajak;
                select.appendChild(opt);
            });
        });
    }
    if (exportAnalisisXLSXBtn) {
        exportAnalisisXLSXBtn.addEventListener("click", () => {
            exportTableToXLSX(
                "analisisDataTable5",
                `hasil_analisis_znt_${new Date().toISOString().slice(0, 10)}.xlsx`
            );
        });
    }
});

// =========================================================================
// --- Fungsi Perhitungan ---
// =========================================================================

function calculateJenisAdjusted(jenis) {
    const penyesuaianPenawaran = dataReferensi.penyesuaian_penawaran !== undefined ? parseFloat(dataReferensi.penyesuaian_penawaran) : 0.9;
    const penyesuaianLelang = dataReferensi.penyesuaian_lelang !== undefined ? parseFloat(dataReferensi.penyesuaian_lelang) : 0.75;
    const penyesuaianAgunan = dataReferensi.penyesuaian_agunan !== undefined ? parseFloat(dataReferensi.penyesuaian_agunan) : 0.85;
    const penyesuaianKeterangan = dataReferensi.penyesuaian_keterangan !== undefined ? parseFloat(dataReferensi.penyesuaian_keterangan) : 0.95;

    switch (jenis) {
        case 'Penawaran': return penyesuaianPenawaran;
        case 'Lelang': return penyesuaianLelang;
        case 'Agunan Bank': return penyesuaianAgunan;
        case 'Keterangan RT/RW/Lurah': return penyesuaianKeterangan;
        default: return 1;
    }
}

function calculateDataKepemilikanAdjusted(kepemilikan) {
    const penyesuaianSertifikat = dataReferensi.penyesuaian_sertifikat !== undefined ? parseFloat(dataReferensi.penyesuaian_sertifikat) : 0.9;

    if (kepemilikan === 'SHM') {
        return 0;
    }
    if (kepemilikan === 'Girik/Belum SHM' || kepemilikan === 'AJB') {
        return penyesuaianSertifikat;
    }
    return 0;
}

function calculateKetinggianAdjusted(ketinggianDariJalan, luas, hargaWajar) {
    const hargaUrug = dataReferensi.harga_tanah_urug !== undefined ? parseFloat(dataReferensi.harga_tanah_urug) : 200000;

    if (ketinggianDariJalan > 0 && hargaWajar > 0) {
        const adjustedValue = (ketinggianDariJalan * hargaUrug) * luas / hargaWajar;
        return adjustedValue;
    }
    return 0;
}

function calculateBentukTanahAdjusted(bentukTanah) {
    const penyesuaianBentukTanah = dataReferensi.penyesuaian_bentuk_tanah !== undefined ? parseFloat(dataReferensi.penyesuaian_bentuk_tanah) : 0.9;

    if (bentukTanah === 'Normal') {
        return 0;
    }
    if (bentukTanah === 'Tidak Beraturan/Ngantong') {
        return penyesuaianBentukTanah;
    }
    return 0;
}

function calculateWaktuAdjusted(tanggalString) {
    const kenaikanPerBulan = dataReferensi.kenaikan_per_bulan !== undefined ? parseFloat(dataReferensi.kenaikan_per_bulan) : 0;
    const currentDate = new Date();
    const inputDate = new Date(tanggalString);

    if (isNaN(inputDate.getTime())) return 0;

    let months;
    months = (currentDate.getFullYear() - inputDate.getFullYear()) * 12;
    months -= inputDate.getMonth();
    months += currentDate.getMonth();

    if (currentDate.getDate() < inputDate.getDate()) {
        months--;
    }

    if (months < 0) return 0;

    const adjustedValue = kenaikanPerBulan * months;

    return adjustedValue;
}

function calculateHargaWajar(
    hargaTransaksi,
    jenisAdjusted,
    waktuAdjusted
) {
    const baseHarga = parseFloat(hargaTransaksi);

    if (isNaN(baseHarga) || baseHarga <= 0) {
        return 0;
    }

    const totalHarga = baseHarga * (1 + jenisAdjusted + waktuAdjusted);

    return Math.max(0, totalHarga);
}

function calculateHargaWajarform3(
    selisihNilai,
    lokasiBakalGrade,
    bentukTanahAdjusted,
    ketinggianAdjusted,
    kepemilikanAdjusted
) {
    // Hitung setiap komponen penyesuaian
    const penyesuaianLokasi3 = selisihNilai * lokasiBakalGrade;
    const penyesuaianBentukTanah3 = selisihNilai * bentukTanahAdjusted;
    const penyesuaianKetinggianTanah3 = selisihNilai * ketinggianAdjusted;
    const penyesuaianKepemilikan3 = selisihNilai * kepemilikanAdjusted;

    // Terapkan rumus untuk mendapatkan Nilai Wajar Objek/ZNT3
    const nilaiWajarObjek3 =
        selisihNilai -
        penyesuaianLokasi3 +
        penyesuaianBentukTanah3 +
        penyesuaianKetinggianTanah3 +
        penyesuaianKepemilikan3;

    return nilaiWajarObjek3;
}

// --- Fungsi Ekspor Tabel ke CSV ---
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) {
        // Menggunakan modal kustom sebagai pengganti alert
        createModal(`Tabel dengan ID "${tableId}" tidak ditemukan.`, () => { });
        return;
    }

    const rows = table.querySelectorAll('tr');
    let csv = [];

    for (const row of rows) {
        const cells = row.querySelectorAll('th, td');
        const rowData = [];

        cells.forEach(cell => {
            let text = cell.innerText.replace(/"/g, '""');
            if (text.includes(',') || text.includes('\n') || text.includes('"')) {
                text = `"${text}"`;
            }
            rowData.push(text);
        });
        csv.push(rowData.join(','));
    }

    const csvString = csv.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });

    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
// ================= EXPORT TABLE TO XLSX (ANALISIS ZNT) =================
function exportTableToXLSX(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) {
        createModal(`Tabel dengan ID "${tableId}" tidak ditemukan.`, () => {});
        return;
    }

    // Clone tabel agar tidak ganggu tampilan asli
    const clonedTable = table.cloneNode(true);

    // Bersihkan styling
    clonedTable.querySelectorAll("th, td").forEach(cell => {
        cell.removeAttribute("class");
        cell.style = "";
    });

    const workbook = XLSX.utils.book_new();
    const worksheet = XLSX.utils.table_to_sheet(clonedTable);

    XLSX.utils.book_append_sheet(workbook, worksheet, "Hasil Analisis ZNT");
    XLSX.writeFile(workbook, filename);
}

// ================= BUTTON HANDLER =================
document.addEventListener("DOMContentLoaded", () => {
    const exportBtn = document.getElementById("exportAnalisisXLSX");

    if (exportBtn) {
        exportBtn.addEventListener("click", () => {
            exportTableToXLSX(
                "analisisDataTable5",
                `hasil_analisis_znt_${new Date().toISOString().slice(0, 10)}.xlsx`
            );
        });
    }
});
