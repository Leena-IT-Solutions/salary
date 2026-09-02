<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeExport implements FromArray, WithHeadings, ShouldAutoSize, WithDrawings, WithStyles
{
    protected $data;
    protected $headings;
    protected $drawingsData;

    public function __construct(array $data, array $headings, array $drawingsData = [])
    {
        $this->data = $data;
        $this->headings = $headings;
        $this->drawingsData = $drawingsData;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function drawings()
    {
        $drawings = [];
        foreach ($this->drawingsData as $item) {
            if (isset($item['path']) && file_exists($item['path']) && filesize($item['path']) > 0) {
                $drawing = new Drawing();
                $drawing->setName('Employee Photo');
                $drawing->setDescription('Employee Photo');
                $drawing->setPath($item['path']);
                $drawing->setHeight(55);
                $drawing->setCoordinates($item['coordinate']);
                $drawing->setOffsetX(6);
                $drawing->setOffsetY(4);
                $drawings[] = $drawing;
            }
        }
        return $drawings;
    }

    public function styles(Worksheet $sheet)
    {
        if (!empty($this->drawingsData)) {
            $totalRows = count($this->data) + 1;
            for ($row = 2; $row <= $totalRows; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(50);
            }
            $sheet->getColumnDimension('A')->setWidth(16);
        }
    }
}
