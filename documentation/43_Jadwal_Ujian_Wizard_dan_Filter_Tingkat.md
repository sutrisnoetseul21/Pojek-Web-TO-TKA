# 43. Jadwal Ujian, Wizard Layout, dan Filter Tingkat

Dokumentasi ini mencatat perubahan besar pada modul **Paket Tryout** dan **Jadwal Tryout** untuk merampingkan pengalaman pengguna (UX), menyelaraskan visual antar modul, dan memperketat logika relasi tingkat kelas untuk mencegah kesalahan penjadwalan.

---

## 1. Perubahan Istilah (Terminologi)
Mengikuti arahan desain yang lebih profesional:
- Rekayasa penamaan di Sidebar Navigation dari `"Tryout"` kini diubah menjadi **`"Ujian"`**.
- **Paket Tryout** $\rightarrow$ **`Paket Ujian`**
- **Jadwal Tryout** $\rightarrow$ **`Jadwal Ujian`**
> [!NOTE]
> Perubahan ini **hanya pada label antarmuka (UI)**. Seluruh skema database, URL route (`/admin/jadwal-tryouts`), dan nama model Eloquent tetap menggunakan kata `tryout` atau `_tryout` agar tidak merusak relasi dan controller eksisting.

---

## 2. Formulir Wizard di Paket Ujian
Formulir pembuatan *Paket Ujian* yang semula menggunakan sistem Tab biasa, kini bermutasi menjadi **Wizard Layout** yang melangkah setahap demi setahap.
1. **Langkah 1**: Keterangan Paket & Sekolah.
2. **Langkah 2**: Detail Informasi (Jenjang & Tingkat).
3. **Langkah 3**: Pengaturan Tambahan.

Ini diadopsi agar serupa dengan desain formulir pada modul *Bank Soal*, meningkatkan konsistensi UX.

---

## 3. Integrasi Kolom `Tingkat`
Untuk merinci target ujian (misal SMP Kelas 7, 8, atau 9), kolom database `tingkat` diimbuhkan ke tabel `paket_tryout`.

### Mekanisme Filter Cerdas:
- Pilihan **Tingkat** akan otomatis menyesuaikan diri dengan **Jenjang** yang dipilih.
  - SD $\rightarrow$ Tingkat 1 - 6
  - SMP $\rightarrow$ Tingkat 7 - 9
  - SMA/SMK $\rightarrow$ Tingkat 10 - 12
- Aturan ini berlaku di dalam formulir Create/Edit maupun row **Advanced Filter** di halaman Index Tabel.

---

## 4. Desain Formulir Jadwal Ujian
Penjadwalan ujian kini memiliki ketergantungan logika (*Dependent Form Selection*) yang sangat intuitif:

### filter Paket Terpilih:
- Ditambahkan field **`jenjang`** dan **`tingkat`** (Bersifat *non-dehydrated* / Tidak disimpan ke DB).
- Jika Anda memilih salah satu dari field ini, kolom **Paket Ujian** di bawahnya hanya akan menampilkan Paket yang cocok dengan level tersebut.
- Sebaliknya: Jika Anda mengisi **Paket Ujian** terlebih dahulu, kolom `jenjang` dan `tingkat` akan melakukan **Auto-Populate** (mengisi diri sendiri) menyesuaikan isi paket tersebut.

### Filter Target Kelas:
- Kolom **Target Kelas** sekarang memiliki kueri penyaring yang mengikat.
- Memilih Tingkat `7` akan membuat kotak pilihan **hanya menampilkan kelas-kelas yang duduk di Tingkat 7**. Menutup potensi admin merilis jadwal ujian SMP Kelas 7 ke baris kelas 8.

---

## 5. Tombol Pintas: Pilih Semua Kelas
Pada formulir Jadwal Ujian, di atas kolom `Target Kelas` imajiner multiselect, diselipkan sebuah **Checkbox: `"Pilih Semua Kelas di Tingkat Ini"`**.

-  Mencentang kotak ini akan menyapu dan memasukkan seluruh daftar ID kelas yang sedang aktif di tingkat tersebut ke dalam target ujian secara serentak.
-  Sangat menghemat waktu untuk sekolah paralel dengan banyak kelas per tingkat.

---

## 6. Penghapusan Kuota Peserta
Input **`Kuota Peserta`** dihapus dari formulir pembuatan Jadwal.

**Alasan Arsitektural**:
Kapasitas dan perizinan masuk ujian sesungguhnya telah dikomandoi secara realtime oleh Proktor/Admin pada halaman monitor **Kelompok Tes** (menggunakan fungsi *Assign / Unassign*). Static-quota di formulir creation adalah batasan redundant/berlebih yang tidak dibutuhkan.
