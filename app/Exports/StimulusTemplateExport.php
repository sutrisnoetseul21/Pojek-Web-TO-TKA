<?php

namespace App\Exports;

use App\Models\RefMapel;
use App\Models\RefPaketSoal;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Illuminate\Support\Collection;

class StimulusTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Form Stimulus' => new StimulusFormSheet(),
            'Referensi Data' => new ReferenceDataSheet(),
        ];
    }
}

class StimulusFormSheet implements WithTitle, WithHeadings, WithEvents
{
    public function title(): string
    {
        return 'Form Stimulus';
    }

    public function headings(): array
    {
        return [
            'MAPEL (PILIH)',
            'PAKET (PILIH - OPSIONAL)',
            'JUDUL STIMULUS',
            'TIPE (TEKS/GAMBAR)',
            'KONTEN WACANA (HTML)',
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
                $validationA->setFormula1("'Referensi Data'!\$A\$2:\$A\$100");
                
                for ($i = 2; $i <= 100; $i++) {
                    $sheet->getCell('A'.$i)->setDataValidation(clone $validationA);
                }

                // Dropdown untuk Paket (Kolom B)
                $validationB = $sheet->getCell('B2')->getDataValidation();
                $validationB->setType(DataValidation::TYPE_LIST);
                $validationB->setAllowBlank(true);
                $validationB->setFormula1("'Referensi Data'!\$B\$2:\$B\$100");
                for ($i = 2; $i <= 100; $i++) {
                    $sheet->getCell('B'.$i)->setDataValidation(clone $validationB);
                }

                // Dropdown untuk Tipe (Kolom D) - Statis
                $validationD = $sheet->getCell('D2')->getDataValidation();
                $validationD->setType(DataValidation::TYPE_LIST);
                $validationD->setFormula1('"TEKS,AUDIO,VIDEO,GAMBAR"');
                for ($i = 2; $i <= 100; $i++) {
                    $sheet->getCell('D'.$i)->setDataValidation(clone $validationD);
                }

                // Set Column Widths
                $sheet->getColumnDimension('A')->setWidth(35);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(60);
            },
        ];
    }
}

class ReferenceDataSheet implements WithTitle, WithHeadings, FromCollection
{
    public function title(): string
    {
        return 'Referensi Data';
    }

    public function headings(): array
    {
        return ['Daftar Mapel (ID - Nama - Jenjang)', 'Daftar Paket (ID - Nama)'];
    }

    public function collection()
    {
        $mapels = RefMapel::all()->map(fn($m) => "{$m->id} - {$m->nama_mapel} - {$m->jenjang}")->toArray();
        $pakets = RefPaketSoal::all()->map(fn($p) => "{$p->id} - {$p->nama_paket}")->toArray();

        $maxCount = max(count($mapels), count($pakets));
        $data = [];

        for ($i = 0; $i < $maxCount; $i++) {
            $data[] = [
                $mapels[$i] ?? '',
                $pakets[$i] ?? '',
            ];
        }

        return collect($data);
    }
}
