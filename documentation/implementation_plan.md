
---

# Implementation Plan - Reorganizing Sidebar Menu

Structuring the Filament sidebar for better usability and logical grouping.

## Proposed Changes

### [Filament Resources & Pages]
I will update the following files to set `navigationGroup` and `navigationSort`:

#### 1. Tryout
- `PaketTryoutResource.php` (Sort 1)
- `JadwalTryoutResource.php` (Sort 2)

#### 2. Bank Soal & Mata Pelajaran
- `RefMapelResource.php` (Sort 1)
- `BankSoalResource.php` (Sort 2)
- `BankStimulusResource.php` (Sort 3)
- `RefPaketSoalResource.php` (Sort 4, Label: "Kategori Soal")

#### 3. Monitoring Ujian
- `PesertaJadwalResource.php` (Sort 1, Label: "Peserta Sedang Tes")
- `MonitoringSesi.php` (Sort 2)
- `UjianActivityLogResource.php` (Sort 3)
- `BantuanPesertaResource.php` (Sort 4)

#### 4. Laporan / Hasil
- `HasilSementara.php` (Sort 1)
- `HasilTryout.php` (Sort 2)

#### 5. Manajemen Peserta
- `SekolahResource.php` (Sort 1)
- `KelasResource.php` (Sort 2)
- `UserResource.php` (Sort 3)
- `KartuPesertaResource.php` (Sort 4)

#### 6. Manajemen User
- `UserAdminResource.php` (Sort 1)
- `UserSuperAdminResource.php` (Sort 2)

## Verification Plan

### Manual Verification
1.  **Check Sidebar**: Log in as Super Admin and verify the group names and item order match the request.
