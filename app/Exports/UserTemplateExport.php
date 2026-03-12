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
    public function title(): string
    {
        return 'Form User';
    }

    public function headings(): array
    {
        return [
            'NAMA LENGKAP (WAJIB)',
            'SEKOLAH (OPSIONAL)',
            'JENIS KELAMIN (L/P) (OPSIONAL)',
            'USERNAME (OPSIONAL - KOSONGKAN UNTUK OTOMATIS)',
            'PASSWORD (OPSIONAL - KOSONGKAN UNTUK OTOMATIS)',
        ];
    }

    public function array(): array
    {
        return [
            // Contoh 1: Diisi penuh
            ['Budi Santoso', 'SMA 1 Jakarta', 'L', 'P2026001', 'BUDI*'],
            // Contoh 2: Autogenerate
            ['Andi Hermanto', 'SMA 2 Bandung', 'L', '', ''],
            // Contoh 3: Autogenerate dengan Nama & Sekolah
            ['Siti Aminah', 'SMA 3 Surabaya', 'P', '', ''],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Dropdown untuk Jenis Kelamin (Kolom C)
                $validationC = $sheet->getCell('C2')->getDataValidation();
                $validationC->setType(DataValidation::TYPE_LIST);
                $validationC->setErrorStyle(DataValidation::STYLE_STOP);
                $validationC->setAllowBlank(true);
                $validationC->setShowDropDown(true);
                $validationC->setFormula1('"L,P"');
                
                for ($i = 2; $i <= 500; $i++) {
                    $sheet->getCell('C'.$i)->setDataValidation(clone $validationC);
                }

                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(35);
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
