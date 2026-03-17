# Masterplan - Menu Kelompok Tes (Manajemen Proktor)

Menu ini merupakan gateway akses utama bagi peserta untuk dapat mengikuti ujian, yang dirancang identik dengan alur kerja sistem ANBK.

---

## 🔍 1. Tujuan & Fungsi Utama

Berfungsi untuk **mengaktifkan atau menonaktifkan** peserta pada kelompok tes tertentu agar mereka dapat melakukan login ke laman ujian.

### Fitur Utama Proktor:
| Fitur | Deskripsi | Efek Status |
| :--- | :--- | :--- |
| **Assign** | Mengaktifkan peserta agar bisa login | `Non Active` ➡️ `Active` |
| **Unassign** | Menonaktifkan peserta yang belum login/tidak di daftar tes | `Active` ➡️ `Non Active` |
| **Reset All** | Menonaktifkan seluruh peserta secara masal | Semua ➡️ `Non Active` |

---

## 🛠️ 2. Langkah Kerja Proktor (Assign Peserta)

Untuk mengaktifkan peserta, proktor harus mengikuti langkah sekuensial berikut:

1.  **Rilis Token**: Proktor merilis token ujian terlebih dahulu sesuai kelompok tes.
2.  **Filter Data**:
    *   Pilih **Status**: `Non Active`
    *   Pilih **Kode Tes**: (Dropdown sesuai rilis token)
    *   Pilih **Kelompok**: (Dropdown kelompok tes)
3.  **Apply Filter**: Mengklik tombol **Apply** untuk memuat daftar peserta.
4.  **Atur Tampilan**: Jika peserta > 10, ubah tampilan `Display` di pojok kanan bawah ke angka terbesar untuk melihat semua item.
5.  **Bulk Select**: Centang checkbox pada header tabel untuk memilih semua peserta.
6.  **Eksekusi**: Mengklik tombol **Assign** untuk mengaktifkan seluruh peserta terpilih.
7.  **Konfirmasi**: Menjawab `Yes` pada pop-up konfirmasi hingga muncul pop-up informasi berhasil.

---

## 💾 3. Kebutuhan Struktur Data & Interface (Estimasi)

Untuk mengimplementasikan dashboard proktor ini, dibutuhkan:

1.  **Komponen UI (Filament)**:
    *   **Filter Section**: Komponen `Select` (Status, Kode Tes, Kelompok) + `Button` (Apply).
    *   **Action Buttons**: Button `Assign`, `Unassign`, dan `Reset` di atas tabel.
    *   **Bulk Actions**: Integrasi `Checkbox` di setiap baris tabel.
    *   **Table Columns**: `Status` (badge), `Kode Test`, `Username`, `Nama Peserta`, `Kelompok`.

2.  **Logika Backend (Laravel)**:
    *   Aksi Update Masal: `PesertaJadwal::whereIn('id', $selectedIds)->update(['status' => 'active'])`
    *   Guard / Aturan: Hanya mengizinkan `Unassign` jika user belum memiliki sesi aktif atau belum menjawab soal.

---

*Dokumen ini dibuat sebagai panduan sekuensial untuk pengerjaan menu 'Kelompok Tes' tahap berikutnya.*
