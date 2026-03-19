# 38. Analisis Konsep & Plan: Fitur Ruangan (Dinamis)

Dokumen ini berisi analisis dan rencana implementasi penambahan fitur **Ruangan** dengan pendekatan dinamis/transaksional, di mana ruangan dialokasikan pada setiap Jadwal Ujian.

---

## 1. Analisis Konsep

### A. Kondisi Saat Ini (As-Is)
*   **Hierarki Peserta**: `Sekolah` ➔ `Kelas` ➔ `User` (Peserta).
*   **Penjadwalan**: `JadwalTryout` menargetkan `Sekolah` dan multi `Kelas` (via tabel pivot `jadwal_tryout_kelas`).
*   **Absen**: Belum ada konsep **lokasi fisik** atau **ruangan** tempat ujian berlangsung.
*   **Dampak**: 
    1. Cetak Kartu Peserta belum mencantumkan lokasi ruang ujian.
    2. Monitoring ujian hanya bisa difilter per kelas, belum bisa per ruangan.

### B. Kebutuhan Fitur (To-Be)
*   **Master Data**: Menampung daftar ruangan (nama, kapasitas, lokasi).
*   **Alokasi**: Menempatkan peserta ke ruangan tertentu pada jadwal tertentu.
*   **Output**: Menampilkan info Ruangan pada Kartu login & filter monitoring.

---

## 2. Hubungan Ruangan dan Kelas (PENTING)

Ada dua pendekatan, dan demi kebutuhan ujian, dipilihlah **Pendekatan Dinamis**:

*   **Logika**: **Tidak ada relasi langsung** antara `Kelas` dan `Ruangan` di data master. Relasi terjadi di level **Siswa** pada saat **Ujian/Jadwal**.
*   **Keunggulan**: 
    *   **Fleksibel**: Bisa memecah 1 kelas ke 2 ruangan jika kapasitas komputer lab terbatas.
    *   **Berbagi Lab**: Bisa menggabungkan kelas yang berbeda ke 1 ruangan besar (Aula).
    *   **Per Sesi**: Ruangan bisa dipakai bergantian oleh kelas lain di jam berbeda.

---

## 3. Desain Arsitektur & Database

### A. Tabel Baru: `ruangan` (Master Data)
| Kolom | Tipe | Keterangan |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Auto-increment |
| `sekolah_id` | BigInt (FK) | Terikat pada Sekolah |
| `nama_ruangan`| String | ex: "Lab Komputer A", "Ruang 201" |
| `kode_ruangan`| String (Unique)| ex: "LAB-A", "R201" |
| `kapasitas`   | Integer | Kuota maksimal ruangan |
| `keterangan`  | Text | Catatan tambahan |

### B. Modifikasi Tabel: `peserta_jadwal`
Menambahkan `ruangan_id` untuk mencatat posisi duduk peserta pada jadwal tersebut.
| Kolom Tambahan | Tipe | Keterangan |
| :--- | :--- | :--- |
| `ruangan_id` | BigInt (FK, Nullable) | Relasi ke tabel `ruangan` |

---

## 4. Rencana Implementasi (Sesuai Persetujuan)

### 📅 Tahap 1: Database & Model
1.  **Migration**: Buat tabel `ruangan` dan alter `peserta_jadwal`.
2.  **Model Relasi**:
    *   `Sekolah` ➔ hasMany ➔ `Ruangan`
    *   `Ruangan` ➔ hasMany ➔ `PesertaJadwal`
    *   `PesertaJadwal` ➔ belongsTo ➔ `Ruangan`

### 🖥️ Tahap 2: Filament Admin Panel
1.  **`RuanganResource`**: CRUD master data ruangan (Scoped ke sekolah admin).
2.  **Alokasi Sesi**:
    *   Di halaman Monitoring atau Jadwal, buat **Bulk Action** `"Alokasi Ruangan"`.
    *   Saran: Tambahkan dropdown/select di baris tabel peserta untuk edit cepat.

### 📄 Tahap 3: Pelaporan & Kartu
1.  Update query `KartuPesertaController` agar me-load relasi `ruangan` dari pivot `peserta_jadwal`.
2.  Tambahkan field `"Ruangan"` di template `kartu-peserta.blade.php`.

---

> [!TIP]
> **Praktik Terbaik**: Pembagian ruangan pada level jadwal (`peserta_jadwal`) sangat aman karena tidak mengikat data statis siswa, sehingga data histori ujian tetap akurat meski siswa pindah-pindah ruang di ujian berbeda.
