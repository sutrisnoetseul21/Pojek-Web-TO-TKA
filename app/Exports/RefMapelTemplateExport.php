<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class RefMapelTemplateExport implements FromArray, WithTitle, WithHeadings, WithEvents
{
    protected $jenjang;

    public function __construct($jenjang = null)
    {
        $this->jenjang = $jenjang;
    }

    public function title(): string
    {
        return 'Template Mata Pelajaran';
    }

    public function headings(): array
    {
        return [
            'NAMA MAPEL',
            'KODE MAPEL',
            'JENJANG (SD/SMP/SMA/SMK/UMUM)',
        ];
    }

    public function array(): array
    {
        $jenjangExample = $this->jenjang ?? 'SMP';

        return [
            ['Matematika', 'MAT01', $jenjangExample],
            ['Bahasa Indonesia', 'BIN01', $jenjangExample],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Dropdown untuk Jenjang (Kolom C)
                $validationC = $sheet->getCell('C2')->getDataValidation();
                $validationC->setType(DataValidation::TYPE_LIST);
                $validationC->setAllowBlank(false);
                $validationC->setShowDropDown(true);
                $validationC->setFormula1('"SD,SMP,SMA,SMK,UMUM"');

                for ($i = 2; $i <= 100; $i++) {
                    $sheet->getCell('C'.$i)->setDataValidation(clone $validationC);
                }

                $sheet->getStyle('A1:C1')->getFont()->setBold(true);
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
            },
        ];
    }
}
