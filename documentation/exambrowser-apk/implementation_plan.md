# Implementation Plan - Android ExamBrowser (CBT) (Updated)

Membuat aplikasi Android berbasis **Kotlin (Native)** yang bertindak sebagai browser aman (*Safe Exam Browser*) untuk ujian online. Aplikasi ini bersifat **Generik** dan memiliki perlindungan keamanan untuk mencegah kecurangan.

---

## 💡 Konfirmasi Kebutuhan
*   **Sifat Generik**: Mendukung input URL apa saja.
*   **Keluar Otomatis (Opsional)**: Terintegrasi via `JavascriptInterface`.
*   **Keluar Darurat (Backup)**: Tombol Rahasia (Secret Tap) + PIN.

---

## 🛠️ Desain Arsitektur & Teknologi

*   **Bahasa**: Kotlin (Android Native)
*   **Minimum SDK**: API 21 (Android 5.0 - Lollipop)
*   **Komponen Utama**:
    *   `HomeActivity`: Dashboard untuk Input URL & Scanner QR Code.
    *   `ExamActivity`: `WebView` dengan *Immersive Mode* & `FLAG_SECURE`.
    *   `JavascriptInterface`: Komunikasi Web -> Android.
    *   `KioskManager`: Helper `startLockTask()` (Screen Pinning).

### 🔑 Izin Akses (Permissions)
Wajib dideklarasikan di `AndroidManifest.xml`:
*   `android.permission.INTERNET`: Untuk memuat halaman ujian.
*   `android.permission.CAMERA`: Untuk memindai QR Code *(Membutuhkan Runtime Permission untuk Android 6.0+)*.

---

## 📋 Tahapan Implementasi

### 1. Dashboard & Navigasi
*   Interface Dashboard yang bersih untuk Sekolah/Instansi mana pun.
*   Input Text URL & Validasi format.
*   Fitur Scan QR Code menggunakan kamera.

### 2. WebView & Keamanan (Kiosk Mode)
*   Mengaktifkan *Immersive Mode Sticky* (sembunyikan status bar).
*   Mencegah screenshot/record (`getWindow().setFlags(FLAG_SECURE)`).
*   **Penanganan Error Internet (PENTING)**: Menggunakan `WebViewClient (onReceivedError)` untuk mendeteksi putusnya koneksi. Menampilkan **Halaman HTML Lokal** (Offline Page) agar tidak memuat link/search dari browser bawaan.
*   Mengaktifkan **App Pinning** (`startLockTask()`) saat ujian dimulai.

### 3. Exit Strategy (Sistem Pintu Keluar)
*   **Otomatis**: Menangkap sinyal dari `JavascriptInterface`.
*   **Manual/Darurat**: Ketuk 5x pada logo + input PIN (Default: `1234`).
*   **Pembersihan Data (Clear Session)**:
    Tepat sebelum memanggil fungsi `finish()` (menutup aplikasi), jalankan:
    *   `WebStorage.getInstance().deleteAllData()`
    *   `CookieManager.getInstance().removeAllCookies(null)`
    *   *Alasan:* Menjamin sesi siswa sebelumnya terhapus total untuk sesi berikutnya (tidak auto-login).

---

## 🧪 Rencana Verifikasi (Pengujian)

1.  **Uji Error Handing**: Mematikan WiFi di tengah ujian (Pastikan muncul halaman error lokal yang aman).
2.  **Uji Fitur Exit**: Memastikan setelah keluar, website meminta login ulang (Berarti Cache & Cookies berhasil terhapus).
3.  **Uji Keamanan**: Cek anti-screenshot dan pembatasan tombol navigasi.

---

## ⚠️ Perhatian Khusus & Catatan Realistis (`startLockTask()`)
*   **Fungsi Android Standar**: fitu `startLockTask()` tanpa konfigurasi Device Owner (MDM) menggunakan mekanisme **Screen Pinning**.
*   **Kelemahan**: Siswa masih bisa menekan tombol `Back + Recent` untuk keluar dari mode pinning.
*   **Kabar Baiknya**: Ketika mode ini dipaksa keluar, **Android akan otomatis mengunci layar perangkat (Device Lock)**. Siswa harus memasukkan Pola/PIN HP mereka untuk masuk kembali. Ini sangat cukup memberi sinyal "kecurangan" kepada pengawas dan mencegah membuka aplikasi lain secara sembunyi-sembunyi.
