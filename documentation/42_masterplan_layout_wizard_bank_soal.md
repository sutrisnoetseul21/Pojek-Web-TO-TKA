# Masterplan - Penerapan Wizard Layout pada Bank Soal

## 1. Analisis Masalah
Saat ini, halaman **Create/Edit Bank Soal** memiliki struktur formulir yang sangat panjang dalam satu halaman tunggal (long scroll page) yang memuat:
1. Identitas & Pengaturan Soal.
2. Editor Teks Pertanyaan & Pembahasan (Tiptap).
3. Grid/Repeater Opsi Jawaban & Poin dinamis.

Tampilan yang menumpuk dari atas ke bawah ini bisa **membingungkan atau melelahkan** bagi guru saat menginput soal, karena area pengerjaan konten seringkali terhimpit.

---

## 2. Solusi Desain: Filament Wizard (2-Step)
Untuk menyingkat tampilan dan membuatnya lebih bersih, kita akan merombak layout `Form` utama pada `BankSoalResource` menggunakan komponen bawaan Filament `Forms\Components\Wizard`. Formulir akan dipecah menjadi **2 Langkah Berjenjang**:

### 🌟 **Step 1: Identitas & Pengaturan**
Fokus mengatur metadata awal soal.
- **Mata Pelajaran**: Select (Wajib ditentukan pertama).
- **Paket Soal**: Select (Terfilter berdasarkan mapel).
- **Tipe Soal**: Select (Menentukan reaktivitas form jawaban di step berikutnya).
- **Stimulus (Induk)**: Select.
- **Bobot Nilai**: TextInput.

---

### 📝 **Step 2: Konten Pertanyaan & Opsi Jawaban**
Menggabungkan area editing utama dalam satu layar kerja luas.
- **Pertanyaan**: Editor Tiptap (Tinggi 200px) + Action Math.
- **Opsi Jawaban**: Grid/Repeater yang isinya dinamis menyesuaikan tipe soal pilihan ganda, kompleks, menjodohkan, atau benar/salah yang sudah dikunci dari Step 1.
- **Pembahasan**: Editor Tiptap (Tinggi 200px) + Action Math.

---

## 3. Rencana Eksekusi Teknis

### 🛠️ **Fase Modifikasi** (`BankSoalResource.php`)
1. Membungkus seluruh skema `form()` ke dalam kontainer `Forms\Components\Wizard::make()`.
2. Mengekstrak blok `Section` untuk dipindahkan ke dalam model `Forms\Components\Wizard\Step::make('...')` dengan 2 kontainer terpisah.
3. Memastikan reaktivitas komponen Tiptap dan Repeater di Step 2 berjalan normal saat guru mengganti isian data tanpa harus klik bolak-balik.

### 🖥️ **Kelayakan UI/UX**
- Penggabungan editor dan opsi jawaban dalam satu Langkah (Step 2) membuat guru bisa **terus melihat pertanyaan** mereka saat sedang menyusun baris pilihan jawaban di bagian bawahnya. Ini merupakan pendekatan yang sangat direkomendasikan.

---
> [!TIP]
> **Mengapa 2-Step Sangat Baik:** Dibandingkan 3-step, 2-step menghemat satu kali klik "Next" tambahan. Guru cukup mengatur metadata awal sekali, lalu fokus mengerjakan keseluruhan soal dan kunci jawaban di layar kedua.
