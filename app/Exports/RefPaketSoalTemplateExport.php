<?php

namespace App\Exports;

use App\Models\RefMapel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class RefPaketSoalTemplateExport implements WithMultipleSheets
{
    protected $jenjang;

    public function __construct($jenjang = null)
    {
        $this->jenjang = $jenjang;
    }

    public function sheets(): array
    {
        return [
            'Form Paket' => new PaketFormSheet($this->jenjang),
            'Referensi_Data' => new MapelDataSheet($this->jenjang),
        ];
    }
}

class PaketFormSheet implements WithTitle, WithHeadings, WithEvents, \Maatwebsite\Excel\Concerns\FromArray
{
    protected $jenjang;

    public function __construct($jenjang = null)
    {
        $this->jenjang = $jenjang;
    }

    public function title(): string
    {
        return 'Form Paket';
    }

    public function headings(): array
    {
        return [
            'NAMA PAKET',
            'MATA PELAJARAN',
            'KETERANGAN',
        ];
    }

    public function array(): array
    {
        $query = RefMapel::query();
        if ($this->jenjang) {
            $query->where('jenjang', $this->jenjang);
        }
        $mapel = $query->first();
        $mapelStr = $mapel ? "{$mapel->id} - {$mapel->nama_mapel} - {$mapel->jenjang}" : '';

        return [
            ['Kategori Soal Aljabar', $mapelStr, 'Paket khusus pembahasan aljabar dasar'],
            ['Ujian Akhir Semester 1', $mapelStr, 'Paket soal mandiri'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Dropdown untuk Mapel (Kolom B)
                $validationB = $sheet->getCell('B2')->getDataValidation();
                $validationB->setType(DataValidation::TYPE_LIST);
                $validationB->setErrorStyle(DataValidation::STYLE_STOP);
                $validationB->setAllowBlank(false);
                $validationB->setShowDropDown(true);
                // Referensi ke sheet Referensi_Data Kolom A
                $validationB->setFormula1('Referensi_Data!$A$2:$A$500');
                
                for ($i = 2; $i <= 500; $i++) {
                    $sheet->getCell('B'.$i)->setDataValidation(clone $validationB);
                }

                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(50);

                // Styling header
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4A5568'], // Abu charcoal
                    ],
                ];

                $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
            },
        ];
    }
}

class MapelDataSheet implements WithTitle, WithHeadings, FromCollection
{
    protected $jenjang;

    public function __construct($jenjang = null)
    {
        $this->jenjang = $jenjang;
    }

    public function title(): string
    {
        return 'Referensi_Data';
    }

    public function headings(): array
    {
        return ['Daftar Mata Pelajaran (ID - Nama - Jenjang)'];
    }

    public function collection()
    {
        $query = RefMapel::query();
        if ($this->jenjang) {
            $query->where('jenjang', $this->jenjang);
        }

        $mapels = $query->get()->map(fn($m) => "{$m->id} - {$m->nama_mapel} - {$m->jenjang}")->toArray();

        $data = [];
        foreach ($mapels as $mapel) {
            $data[] = [$mapel];
        }

        return collect($data);
    }
}
