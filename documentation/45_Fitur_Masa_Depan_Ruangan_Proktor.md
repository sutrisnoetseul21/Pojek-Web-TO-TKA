# Fitur Masa Depan — Ruangan & Proktor

> [!NOTE]
> Fitur-fitur di bawah ini **belum dijadwalkan** untuk implementasi. Dokumen ini berfungsi sebagai catatan ide agar tidak hilang dan bisa diambil kapan saja dibutuhkan.

---

## 1. Dashboard Ringkasan Proktor
Widget Livewire khusus yang muncul saat proktor login:
- Nama ruangan yang ditugaskan hari ini
- Stat cards: Total Peserta / Sudah Login / Sedang Mengerjakan / Selesai
- Progress bar real-time
- Akses cepat ke Reset Login dan Force Submit

**Catatan**: Proktor saat ini mengakses menu *Monitoring Ujian*, *Hasil Ujian/Laporan Hasil*, dan bisa juga *Administrasi Tes* — dashboard ini menambahan **bukan menggantikan** menu tersebut.

---

## 2. Token per Ruangan (Konsep ANBK)
Jika ke depan ingin ada sistem token terpisah per ruangan (mirip mekanisme ANBK):
- Tambahkan kolom `token_ruangan` di tabel `jadwal_ruangan_proktor`
- Setiap ruangan bisa punya token sendiri yang di-generate independen
- Peserta memasukkan token sesuai ruangan mereka

**Lokasi kolom**: Tabel `jadwal_ruangan_proktor` — sudah disiapkan tanpa unique constraint sehingga fleksibel.

---

## 3. Notifikasi Real-time untuk Proktor
Push notification ke dashboard proktor:
- "Peserta X meminta reset login" (integrasi Request Reset)
- "Waktu ujian tinggal 10 menit"
- "Peserta Y terdeteksi tidak aktif selama 5 menit"

**Opsi teknis**: Livewire polling (pola sudah ada di monitoring pages) atau Laravel Echo + Pusher/WebSocket.

---

## 4. Log Audit Khusus Aksi Proktor
Tabel baru `proktor_activity_log`:
- `proktor_id`, `action_type`, `target_user_id`, `jadwal_id`, `ruangan_id`, `detail`, `timestamp`
- Mencatat setiap: Reset Login, Force Submit, Toggle status peserta
- Berguna untuk laporan berita acara ujian

---

## 5. Cetak Denah Ruangan
Berdasarkan data alokasi peserta ke ruangan:
- PDF: Daftar peserta per ruangan (ditempel di pintu)
- Info proktor penanggung jawab
- Integrasi ke Kartu Login → menampilkan nama ruangan

---

## 6. Multi-Proktor per Ruangan
Saat ini 1 ruangan = 1 proktor sudah cukup. Namun secara teknis tabel `jadwal_ruangan_proktor` sudah mendukung multiple record (tanpa unique constraint), sehingga di masa depan bisa ditambahkan:
- Proktor Utama + Proktor Pendamping
- Pembagian shift (pagi/siang)
