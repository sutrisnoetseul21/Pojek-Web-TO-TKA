<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UserTemplateExport implements WithTitle, WithHeadings, WithEvents, \Maatwebsite\Excel\Concerns\FromArray
{
    protected $sekolahId;
    protected $kelasId;

    public function __construct($sekolahId = null, $kelasId = null)
    {
        $this->sekolahId = $sekolahId;
        $this->kelasId = $kelasId;
    }

    public function title(): string
    {
        return 'Form User';
    }

    public function headings(): array
    {
        return [
            'NAMA LENGKAP (WAJIB)',
            'JENIS KELAMIN (L/P) (OPSIONAL)',
            'KELAS (WAJIB JIKA TIDAK PILIH SAAT DOWNLOAD)',
            'USERNAME (OPSIONAL - KOSONGKAN UNTUK OTOMATIS)',
            'PASSWORD (OPSIONAL - KOSONGKAN UNTUK OTOMATIS)',
        ];
    }

    public function array(): array
    {
        $kelasDefault = '';
        if ($this->kelasId) {
            $kelas = \App\Models\Kelas::find($this->kelasId);
            $kelasDefault = $kelas ? $kelas->nama_kelas : '';
        }

        return [
            // Contoh 1: Diisi penuh
            ['Budi Santoso', 'L', $kelasDefault ?: '8-A', 'P2026001', 'BUDI*'],
            // Contoh 2: Autogenerate
            ['Andi Hermanto', 'L', $kelasDefault ?: '8-B', '', ''],
            // Contoh 3: Autogenerate dengan Nama
            ['Siti Aminah', 'P', $kelasDefault ?: '8-A', '', ''],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Dropdown untuk Jenis Kelamin (Kolom B)
                $validationB = $sheet->getCell('B2')->getDataValidation();
                $validationB->setType(DataValidation::TYPE_LIST);
                $validationB->setErrorStyle(DataValidation::STYLE_STOP);
                $validationB->setAllowBlank(true);
                $validationB->setShowDropDown(true);
                $validationB->setFormula1('"L,P"');
                
                // Dropdown untuk Kelas (Kolom C)
                $validationC = null;
                if ($this->sekolahId) {
                    $kelasOptions = \App\Models\Kelas::where('sekolah_id', $this->sekolahId)
                        ->pluck('nama_kelas')
                        ->toArray();
                    
                    if (!empty($kelasOptions)) {
                        $validationC = $sheet->getCell('C2')->getDataValidation();
                        $validationC->setType(DataValidation::TYPE_LIST);
                        $validationC->setErrorStyle(DataValidation::STYLE_STOP);
                        $validationC->setAllowBlank(true);
                        $validationC->setShowDropDown(true);
                        $validationC->setFormula1('"' . implode(',', $kelasOptions) . '"');
                    }
                }

                for ($i = 2; $i <= 500; $i++) {
                    $sheet->getCell('B'.$i)->setDataValidation(clone $validationB);
                    if ($validationC) {
                        $sheet->getCell('C'.$i)->setDataValidation(clone $validationC);
                    }
                }

                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(35);
                $sheet->getColumnDimension('D')->setWidth(45);
                $sheet->getColumnDimension('E')->setWidth(45);

                // Menambahkan styling header
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4A5568'], // Abu charcoal
                    ],
                ];

                $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
            },
        ];
    }
}
