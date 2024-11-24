<?php

namespace App\Exports;

use App\Models\Inventory;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CurrentInventoryReport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Inventory::with('categories', 'subcategories', 'units')
            ->where('quantity', '>', 0)
            ->get()
            ->map(function ($inventory) {
                $units = Unit::all()->keyBy('id');
                $unit = $units[$inventory->unit_id] ?? '';
                return [
                    'ID' => $inventory->id,
                    'Category' => $inventory->categories->name,
                    'Subcategory' => $inventory->subcategories->name,
                    'Quantity' => $inventory->quantity . ($unit ? " {$unit->abbreviation}" : ''),
                    'Created At' => $inventory->created_at->format('M d, Y'),
                ];
            });
    }

    public function headings(): array
    {
        $data = $this->collection();
        $fromDate = $data->min('Created At');
        $toDate = $data->max('Created At');

        return [
            ['Lexerl Trading - Current Inventory'],
            ["From: {$fromDate}"],
            ["To: {$toDate}"],
            [
                'ID',
                'Category',
                'Subcategory',
                'Quantity',
                'Created At',
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1:E1')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 18,
            ]
        ]);

        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');
        $sheet->getStyle('A2:E3')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'italic' => true,
                'size' => 12,
            ],
        ]);

        $sheet->getStyle('A4:E4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
        ]);

        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A4:E{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ],
            ],
        ]);

        return [
            4   => ['font' => ['bold' => true, 'size' => 12]],
            'A' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}
