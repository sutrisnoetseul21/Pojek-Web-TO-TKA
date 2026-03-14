<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Enums\Jenjang;

class KelasTemplateExport implements WithTitle, WithHeadings, WithEvents, \Maatwebsite\Excel\Concerns\FromArray
{
    protected $sekolahId;

    public function __construct($sekolahId = null)
    {
        $this->sekolahId = $sekolahId;
    }

    public function title(): string
    {
        return 'Form Kelas';
    }

    public function headings(): array
    {
        return [
            'NAMA KELAS (WAJIB)',
            'JENJANG (OPSIONAL - KOSONGKAN UNTUK IKUT SEKOLAH)',
            'KETERANGAN',
        ];
    }

    public function array(): array
    {
        return [
            ['10-IPA-1', 'SMA', 'Kelas Unggulan'],
            ['10-IPS-1', 'SMA', 'Kelas Reguler'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Dropdown untuk Jenjang (Kolom B)
                $jenjangs = collect(Jenjang::cases())->map(fn($j) => $j->value)->implode(',');
                
                $validationB = $sheet->getCell('B2')->getDataValidation();
                $validationB->setType(DataValidation::TYPE_LIST);
                $validationB->setErrorStyle(DataValidation::STYLE_STOP);
                $validationB->setAllowBlank(true);
                $validationB->setShowDropDown(true);
                $validationB->setFormula1('"' . $jenjangs . '"');
                
                for ($i = 2; $i <= 500; $i++) {
                    $sheet->getCell('B'.$i)->setDataValidation(clone $validationB);
                }

                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(50);

                // Menambahkan styling header
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4A5568'], // Abu charcoal
                    ],
                ];

                $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
            },
        ];
    }
}
