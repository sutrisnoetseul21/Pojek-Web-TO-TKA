# Panduan Push ke GitHub (CLI)

Dokumen ini berisi langkah-langkah untuk melakukan push (unggah) perubahan kode dari lingkungan lokal ke repository GitHub menggunakan CLI (Terminal).

## 1. Persiapan Awal
Pastikan Anda berada di root direktori proyek:
```bash
cd /home/share-folder/data-ubuntu-24/Documents/Projek-web-Tryout-TKA/TRYOUT-TKA-Bimbel-Excellent
```

## 2. Alur Kerja Git (Standard)

### Langkah 1: Cek Status
Melihat file apa saja yang telah diubah:
```bash
git status
```

### Langkah 2: Tambahkan Perubahan
Menambahkan semua file yang diubah ke area staging:
```bash
git add .
```

### Langkah 3: Commit Perubahan
Gunakan perintah ini untuk mencatat semua perbaikan hari ini dalam satu pesan yang jelas:
```bash
git commit -m "feat: implementasi monitoring & laporan, fix scoring BS, ui refinement nama peserta, & fix crash hasil tryout"
```
Atau jika ingin lebih detail:
```bash
git commit -m "Fix: Penanganan skor Benar-Salah dan sinkronisasi view.
Fix: Error SQL nilai_akhir pada Laporan Hasil Tryout.
Feat: Personalisasi header menggunakan Nama Lengkap peserta.
Update: Dokumentasi proyek dan panduan push GitHub."
```

### Langkah 4: Tarik Perubahan Terbaru (Optional tapi Disarankan)
Untuk menghindari konflik jika ada orang lain yang push ke repo yang sama:
```bash
git pull origin main
```

### Langkah 5: Push ke GitHub
Mengunggah kode Anda ke repository:
```bash
git push origin main
```

---

## 3. Trouble Shooting

### Jika ada Konflik (Merge Conflict)
Jika setelah `git pull` muncul pesan konflik, Anda harus membuka file yang berkonflik, memperbaikinya secara manual, lalu:
```bash
git add .
git commit -m "Resolve merge conflicts"
git push origin main
```

### Jika Token/Password Diminta
Gunakan **Personal Access Token (PAT)** GitHub sebagai password jika Anda menggunakan HTTPS.

---
> **Dicatat**: Panduan ini dibuat pada 14 Maret 2026 setelah penyelesaian Phase 11.
