<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Enums\Jenjang;

class SekolahTemplateExport implements WithTitle, WithHeadings, WithEvents, \Maatwebsite\Excel\Concerns\FromArray
{
    public function title(): string
    {
        return 'Form Sekolah';
    }

    public function headings(): array
    {
        return [
            'NAMA SEKOLAH (WAJIB)',
            'NPSN (WAJIB - 8 DIGIT)',
            'JENJANG (WAJIB)',
            'ALAMAT',
        ];
    }

    public function array(): array
    {
        return [
            ['SMAN 1 Jakarta', '12345678', 'SMA', 'Jl. Budi Utomo No.7'],
            ['SMPN 1 Bandung', '87654321', 'SMP', 'Jl. Kebon Jati No.1'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Dropdown untuk Jenjang (Kolom C)
                $jenjangs = collect(Jenjang::cases())->map(fn($j) => $j->value)->implode(',');
                
                $validationC = $sheet->getCell('C2')->getDataValidation();
                $validationC->setType(DataValidation::TYPE_LIST);
                $validationC->setErrorStyle(DataValidation::STYLE_STOP);
                $validationC->setAllowBlank(true);
                $validationC->setShowDropDown(true);
                $validationC->setFormula1('"' . $jenjangs . '"');
                
                for ($i = 2; $i <= 500; $i++) {
                    $sheet->getCell('C'.$i)->setDataValidation(clone $validationC);
                }

                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(40);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(50);

                // Menambahkan styling header
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4A5568'], // Abu charcoal
                    ],
                ];

                $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
            },
        ];
    }
}
