# 35. Optimalisasi Server dan Pengujian Beban (Stress Test)

Dokumen ini menjelaskan dasar perhitungan dan infrastruktur yang dibutuhkan aplikasi ujian agar mampu menampung beban user/siswa secara serentak (100 s/d 1.000+ siswa).

---

## 🚨 Kesalahan Umum: Pengujian Lokal (`artisan serve`)
Sangat **dilarang** melakukan stress-test menggunakan perintah `php artisan serve` di laptop karena:
- Menggunakan server bawaan PHP yang bersifat **single-threaded** (hanya bisa memproses 1 request dalam satu waktu).
- Beban 10 siswa saja sudah bisa membuat server *hang* atau *time out*, bukan karena kualitas coding aplikasi, melainkan karena batas arsitektur server development.

---

## 🛠️ Rekomendasi Arsitektur Production (VPS)

### 1. Server Stack Mutlak
- **Web Server:** Nginx (Sangat cepat menangani request statis/dinamis paralel).
- **PHP Process:** PHP-FPM minimal versi 8.3 / 8.4.
- **Cache / Session Engine:** **Redis** (Wajib untuk 500+ siswa agar tidak menyiksa database mysql saat session checking).

### 2. Pendorong Akses Ekstrim (Laravel Octane)
Sangat direkomendasikan menginstal **Laravel Octane** (menggunakan driver **Swoole** atau **RoadRunner**) jika ujian dilakukan serentak di hari yang sama:
> **Kenapa?** Aplikasi pre-loaded ke memori RAM, menghilangkan delay boost-up view/middleware/db di setiap request. Kecepatan bisa naik 2 - 5x lipat.

---

## 📊 Estimasi Kebutuhan VPS

### 🗄️ Kelas 100 Siswa (Serentak)
- **CPU:** 2 vCore (Lebih baik Dedicated compute)
- **RAM:** 2 GB - 4 GB
- **Stack:** Nginx + PHP-FPM standar.

### 🗄️ Kelas 1.000 Siswa (Serentak)
- **CPU:** 4 s/d 8 vCore (Wajib High-Frequency core)
- **RAM:** 8 GB - 16 GB+
- **Stack:** Nginx + Laravel Octane + Redis Cache + Dedicated MySQL.

---

## 🔬 Cara Pengujian Yang Benar (Stress-Test)
Gunakan alat seperti **`wrk`** atau **`ab` (ApacheBench)** saat aplikasi sudah berada di VPS staging/produksi (menggunakan Nginx):

**Contoh Command Pengujian dengan `wrk`:**
```bash
# Menguji 100 koneksi bersamaan selama 10 detik menggunakan 4 thread
wrk -t4 -c100 -d10s http://nama-domain-vps.com/
```

*Dokumen ini dibuat sebagai acuan arsitektur deployment masa depan.*
