<?php

namespace App\Exports;

use App\Models\Purchase;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseInExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate !== "null" ? Carbon::parse($startDate)->format('Y-m-d') : null;
        $this->endDate = $endDate !== "null" ? Carbon::parse($endDate)->format('Y-m-d') : null;
    }

    public function collection()
    {
        $query = Purchase::with(['units', 'suppliers', 'transactions', 'categories', 'subcategories'])
            ->where('quantity', '>', 0);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('purchase_date', [$this->startDate, $this->endDate]);
        }

        $purchases = $query->get()
            ->map(function ($inventory) {
                return [
                    'ID' => $inventory->id,
                    'Transaction Type' => $inventory->transactions->type ?? '',
                    'Transaction Number' => $inventory->transaction_number,
                    'Purchase Date' => $inventory->purchase_date,
                    'Category' => $inventory->categories->name ?? '',
                    'Subcategory' => $inventory->subcategories->name ?? '',
                    'Supplier Name' => $inventory->suppliers->name ?? '',
                    'Quantity' => $inventory->quantity,
                    'UM' => $inventory->units->abbreviation ?? '',
                    'Landed Cost' => '₱' . number_format($inventory->landed_cost, 2),
                    'Amount' => '₱' . number_format($inventory->amount, 2),
                    'Description' => $inventory->description,
                    'Created At' => $inventory->created_at->format('Y-m-d h:i A'),
                ];
            });

        $totalAmount = $purchases->sum(fn($row) => (float) str_replace(['₱', ','], '', $row['Amount']));

        $purchases->push([
            'ID' => '',
            'Transaction Type' => '',
            'Transaction Number' => '',
            'Purchase Date' => '',
            'Category' => '',
            'Subcategory' => '',
            'Supplier Name' => '',
            'Quantity' => '',
            'UM' => '',
            'Landed Cost' => '',
            'Amount' => 'Total: ₱' . number_format($totalAmount, 2),
            'Description' => '',
            'Created At' => '',
        ]);

        return $purchases;
    }

    public function headings(): array
    {
        $purchase_date = Purchase::get();
        $fromDate = $purchase_date->min('purchase_date');
        $toDate = $purchase_date->max('purchase_date');

        return [
            ['Lexerl Trading - Purchase In Report'],
            ["From: " . ($this->startDate ?? $fromDate)],
            ["To: " . ($this->endDate ?? $toDate)],
            [
                'ID',
                'Transaction Type',
                'Transaction Number',
                'Purchase Date',
                'Category',
                'Subcategory',
                'Supplier Name',
                'Quantity',
                'UM',
                'Landed Cost',
                'Amount',
                'Description',
                'Created At',
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge the header title
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1:M1')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 18,
            ]
        ]);

        // Style date range rows
        $sheet->mergeCells('A2:M2');
        $sheet->mergeCells('A3:M3');
        $sheet->getStyle('A2:M3')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'italic' => true,
                'size' => 12,
            ],
        ]);

        // Style the headings row
        $sheet->getStyle('A4:M4')->applyFromArray([
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

        // Get the last row (Total row)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A4:M{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ],
            ],
        ]);

        // Highlight the total row
        $sheet->getStyle("A{$lastRow}:M{$lastRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF99'],
            ],
            'font' => [
                'bold' => true,
            ],
        ]);

        return [
            4   => ['font' => ['bold' => true, 'size' => 12]],
            'A' => ['alignment' => ['horizontal' => 'center']], // Center align column A
        ];
    }
}
