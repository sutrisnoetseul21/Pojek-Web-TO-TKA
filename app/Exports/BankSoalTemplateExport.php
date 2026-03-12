<?php

namespace App\Exports;

use App\Models\RefMapel;
use App\Models\RefPaketSoal;
use App\Models\BankStimulus;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class BankSoalTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Form Soal' => new SoalFormSheet(),
            'Referensi_Data' => new RelasiDataSheet(),
        ];
    }
}

class SoalFormSheet implements WithTitle, WithHeadings, WithEvents, \Maatwebsite\Excel\Concerns\FromArray
{
    public function title(): string
    {
        return 'Form Soal';
    }

    public function headings(): array
    {
        $headings = [
            'MAPEL (PILIH)',
            'KATEGORI / PAKET (PILIH - WAJIB)',
            'STIMULUS (PILIH - OPSIONAL)',
            'TIPE SOAL (PILIH)',
            'PERTANYAAN',
            'PEMBAHASAN',
            'BOBOT SOAL (ANGKA)',
        ];

        // 10 Opsi Dinamis. Opsi 1..10
        // Tiap opsi butuh 2 kolom: Teks Opsi dan Kunci/Skor
        for ($i = 1; $i <= 10; $i++) {
            $headings[] = "TEKS OPSI $i / PERNYATAAN";
            $headings[] = "KUNCI/SKOR OPSI $i (ANGKA/TEKS)";
        }

        return $headings;
    }

    public function array(): array
    {
        $mapel = \App\Models\RefMapel::first();
        $mapelStr = $mapel ? "{$mapel->id} - {$mapel->nama_mapel} - {$mapel->jenjang}" : '';
        
        $paket = \App\Models\RefPaketSoal::first();
        $paketStr = $paket ? "{$paket->id} - {$paket->nama_paket}" : '';

        $stimulus = \App\Models\BankStimulus::first();
        $stimulusStr = $stimulus ? "{$stimulus->id} - {$stimulus->judul}" : '';

        return [
            // Contoh 1: PG Tunggal (Tanpa Stimulus)
            [
                $mapelStr, $paketStr, '', 'PG_TUNGGAL',
                'CONTOH: Siapakah penemu bola lampu?',
                'Thomas Alva Edison mematenkan bola lampu pijar.',
                1,
                'Thomas Alva Edison', 1,
                'Nikola Tesla', 0,
                'Albert Einstein', 0,
                'Isaac Newton', 0,
                'Galileo Galilei', 0,
            ],
            // Contoh 2: PG Kompleks (Dengan Stimulus)
            [
                $mapelStr, $paketStr, $stimulusStr, 'PG_KOMPLEKS',
                'CONTOH: Berdasarkan wacana, manakah yang termasuk hewan mamalia laut?',
                'Paus dan Lumba-lumba menyusui anaknya.',
                1,
                'Paus', 1,
                'Hiu', 0,
                'Lumba-lumba', 1,
                'Kuda Laut', 0,
                '', '',
            ],
            // Contoh 3: Benar Salah (Tanpa Stimulus)
            [
                $mapelStr, $paketStr, '', 'BENAR_SALAH',
                'CONTOH: Tentukan Benar / Salah untuk pernyataan umum berikut!',
                '',
                1,
                'Ibu kota Indonesia adalah Jakarta', 'BENAR',
                'Candi Borobudur terletak di Pulau Bali', 'SALAH',
                'Matahari terbit dari sebelah barat', 'SALAH',
                'Bumi berputar mengelilingi matahari', 'BENAR',
                'Air mendidih pada suhu 100 derajat Celcius', 'BENAR',
                'Gunung tertinggi di dunia adalah Gunung Rinjani', 'SALAH',
                '', '',
            ],
            // Contoh 4: Benar Salah (Dengan Stimulus)
            [
                $mapelStr, $paketStr, $stimulusStr, 'BENAR_SALAH',
                'CONTOH: Berdasarkan ulasan Pantai Glagah, tentukan Benar/Salah untuk pernyataan berikut!',
                'Penjelasan ada di dalam paragraf wacana.',
                1,
                'Gaya penyajian teks menggunakan bahasa nonformal sehingga lebih santai.', 'SALAH',
                'Penyajian gambar pada teks membuat pembaca tertarik.', 'BENAR',
                'Akhir ulasan tidak dilengkapi kalimat ajakan kepada pembaca.', 'SALAH',
                'Pantai Glagah terletak di Kabupaten Kulon Progo.', 'BENAR',
                '', '',
                '', '',
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Dropdown untuk Mapel (Kolom A)
                $validationA = $sheet->getCell('A2')->getDataValidation();
                $validationA->setType(DataValidation::TYPE_LIST);
                $validationA->setErrorStyle(DataValidation::STYLE_STOP);
                $validationA->setAllowBlank(false);
                $validationA->setShowInputMessage(true);
                $validationA->setShowErrorMessage(true);
                $validationA->setShowDropDown(true);
                $validationA->setFormula1('Referensi_Data!$A$2:$A$500');
                
                for ($i = 2; $i <= 500; $i++) {
                    $sheet->getCell('A'.$i)->setDataValidation(clone $validationA);
                }

                // Dropdown untuk Paket (Kolom B)
                $validationB = $sheet->getCell('B2')->getDataValidation();
                $validationB->setType(DataValidation::TYPE_LIST);
                $validationB->setAllowBlank(true);
                $validationB->setShowDropDown(true);
                $validationB->setFormula1('Referensi_Data!$B$2:$B$500');
                for ($i = 2; $i <= 500; $i++) {
                    $sheet->getCell('B'.$i)->setDataValidation(clone $validationB);
                }

                // Dropdown untuk Stimulus (Kolom C)
                $validationC = $sheet->getCell('C2')->getDataValidation();
                $validationC->setType(DataValidation::TYPE_LIST);
                $validationC->setAllowBlank(true);
                $validationC->setShowDropDown(true);
                $validationC->setFormula1('Referensi_Data!$C$2:$C$1000');
                for ($i = 2; $i <= 500; $i++) {
                    $sheet->getCell('C'.$i)->setDataValidation(clone $validationC);
                }

                // Dropdown Tipe Soal (Kolom D) - Statis dari sheet referensi
                $validationD = $sheet->getCell('D2')->getDataValidation();
                $validationD->setType(DataValidation::TYPE_LIST);
                $validationD->setErrorStyle(DataValidation::STYLE_STOP);
                $validationD->setAllowBlank(false);
                $validationD->setShowDropDown(true);
                $validationD->setFormula1('Referensi_Data!$D$2:$D$4');
                for ($i = 2; $i <= 500; $i++) {
                    $sheet->getCell('D'.$i)->setDataValidation(clone $validationD);
                }

                // Lebar Kolom
                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(35);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(50); // Pertanyaan
                $sheet->getColumnDimension('F')->setWidth(40); // Pembahasan
                $sheet->getColumnDimension('G')->setWidth(15); // Bobot

                // Set column width dinamis untk Opsi
                $colIndex = 8; // Kolom 'H'
                for ($i = 1; $i <= 10; $i++) {
                    // Teks Opsi
                    $sheet->getColumnDimensionByColumn($colIndex)->setWidth(35);
                    $colIndex++;
                    // Kunci/Skor Opsi
                    $sheet->getColumnDimensionByColumn($colIndex)->setWidth(20);
                    $colIndex++;
                }

                // Menambahkan styling instruksi ringan padu warna di header
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4A5568'], // Abu charcoal
                    ],
                ];

                // Hitung total kolom (A-G ada 7, ditambah 10x2 = 27 kolom keseluruhan -> Kolom AA)
                $sheet->getStyle('A1:AA1')->applyFromArray($headerStyle);
            },
        ];
    }
}

class RelasiDataSheet implements WithTitle, WithHeadings, FromCollection
{
    public function title(): string
    {
        return 'Referensi_Data';
    }

    public function headings(): array
    {
        return ['Daftar Mapel (ID - Nama)', 'Daftar Kategori / Paket (ID - Nama)', 'Daftar Stimulus (ID - Judul)', 'Tipe Soal'];
    }

    public function collection()
    {
        $mapels = RefMapel::all()->map(fn($m) => "{$m->id} - {$m->nama_mapel} - {$m->jenjang}")->toArray();
        $pakets = RefPaketSoal::all()->map(fn($p) => "{$p->id} - {$p->nama_paket}")->toArray();
        $stimulus = BankStimulus::all()->map(fn($s) => "{$s->id} - {$s->judul}")->toArray();
        $tipeSoal = ['PG_TUNGGAL', 'PG_KOMPLEKS', 'BENAR_SALAH'];

        $maxCount = max(count($mapels), count($pakets), count($stimulus), count($tipeSoal));
        $data = [];

        for ($i = 0; $i < $maxCount; $i++) {
            $data[] = [
                $mapels[$i] ?? '',
                $pakets[$i] ?? '',
                $stimulus[$i] ?? '',
                $tipeSoal[$i] ?? '',
            ];
        }

        return collect($data);
    }
}
