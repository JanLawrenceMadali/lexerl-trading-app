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
        $query = Inventory::with('categories', 'subcategories', 'units')
            ->where('quantity', '>', 0);


        $units = Unit::all()->keyBy('id');

        $inventory = $query->get()
            ->map(function ($inventory) use ($units) {
                $unit = $units[$inventory->unit_id] ?? '';
                return [
                    'ID' => $inventory->id,
                    'Category' => $inventory->categories->name,
                    'Subcategory' => $inventory->subcategories->name,
                    'Quantity' => $inventory->quantity,
                    'UM' => $unit ? $unit->abbreviation : '',
                    'Landed Cost' => '₱' . number_format($inventory->landed_cost, 2),
                    'Total Amount' => '₱' . number_format($inventory->landed_cost * $inventory->quantity, 2),
                    'Created At' => $inventory->created_at->format('Y-m-d h:i A'),
                ];
            });

        $totalAmount = $inventory->sum(fn($row) => (float) str_replace(['₱', ','], '', $row['Total Amount']));

        $inventory->push([
            'ID' => '',
            'Category' => '',
            'Subcategory' => '',
            'Quantity' => '',
            'UM' => '',
            'Landed Cost' => '',
            'Total Amount' => 'Total: ₱' . number_format($totalAmount, 2),
            'Created At' => '',
        ]);

        return $inventory;
    }

    public function headings(): array
    {
        $currentDate = now()->format('Y-m-d');

        return [
            ['Lexerl Trading - Current Inventory Report'],
            ["As of {$currentDate}"],
            [
                'ID',
                'Category',
                'Subcategory',
                'Quantity',
                'UM',
                'Landed Cost',
                'Total Amount',
                'Created At',
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1:H1')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 18,
            ]
        ]);

        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2:H3')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'italic' => true,
                'size' => 12,
            ],
        ]);

        $sheet->getStyle('A3:H3')->applyFromArray([
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
        $sheet->getStyle("A3:H{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ],
            ],
        ]);

        $sheet->getStyle("A{$lastRow}:H{$lastRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF99'],
            ],
            'font' => [
                'bold' => true,
            ],
        ]);

        return [
            3   => ['font' => ['bold' => true, 'size' => 12]],
            'A' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}
