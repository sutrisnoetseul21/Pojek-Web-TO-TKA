# Implementation Plan - Dropdown Sesi Ujian (Kustomisasi)

Tujuan: Mengubah visualisasi pengelompokan jadwal dari **Table Tabs** menjadi **Layout Dropdown di Atas Tabel** agar tidak membludak di layar saat banyak jadwal tryout.

---

## 📅 Perubahan Kode

### 1. Revert Pages (`ManagePesertaJadwals.php`) [MODIFY]
*   **Tujuan**: Menghilangkan fungsi `getTabs()` agar tampilan tab di atas tabel hilang.
*   **Langkah**: Hapus (atau komentari) method `getTabs()` di file `app/Filament/Resources/PesertaJadwalResource/Pages/ManagePesertaJadwals.php`.

---

### 2. Modifikasi Resource Layout (`PesertaJadwalResource.php`) [MODIFY]
*   **Tujuan**: Meletakkan filter di atas konten tabel dan memaksakan "pilihan terlebih dahulu" agar user tidak melihat data bertabrakan di awal buka halaman.
*   **Langkah**:
    1.  Gunakan Layout `AboveContent` (Di atas baris data):
        ```php
        use Filament\Tables\Enums\FiltersLayout;
        // ...
        ->filtersLayout(FiltersLayout::AboveContent)
        ```
    2.  Sesuaikan **Filter `jadwal_tryout_id`** agar mengarah pada *Zero Data* jika belum dipilih (sehingga terkesan harus buka sesi dulu):
        ```php
        // Contoh Query yang disempurnakan:
        ->query(fn (Builder $query, array $data) => 
            $query->where('jadwal_tryout_id', $data['value'] ?? -1)
        )
        ```
        *(Catatan: Menggunakan `-1` memastikan data kosong sebelum dipilih dropdown-nya)*.

---

## ✅ Rencana Verifikasi (Manual)

Untuk memvalidasi perubahan visual:

1.  **Layar Awal**: Buka Dashboard Monitoring, pastikan data list tabel **Kosong** atau bertuliskan *"No Data"* sebelum Anda memilih salah satu Jadwal di dropdown atas.
2.  **Fungsi Seleksi**: Pilih salah satu Jadwal di dropdown (misal: Sesi 4), niscaya data peserta khusus Sesi 4 langsung bermunculan.
3.  **Bebas Clutter**: Row atas tabel sekarang hanya diisi single baris panel dropdown, tidak ada tumpukan tab lagi.
