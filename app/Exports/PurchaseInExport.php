<?php

namespace App\Exports;

use App\Models\Purchases;
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
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $purchases = Purchases::with(['units', 'suppliers', 'transactions', 'categories', 'subcategories'])
            ->where('quantity', '>', 0)
            ->when($this->startDate !== "null" && $this->endDate !== "null", function ($query) {
                $query->whereBetween('purchase_date', [$this->startDate, $this->endDate]);
            })
            ->get()
            ->map(function ($inventory) {
                return [
                    'ID' => $inventory->id,
                    'Transaction Type' => $inventory->transactions->type ?? '',
                    'Transaction Number' => $inventory->transaction_number,
                    'Purchase Date' => $inventory->purchase_date,
                    'Category' => $inventory->categories->name ?? '',
                    'Subcategory' => $inventory->subcategories->name ?? '',
                    'Supplier Name' => $inventory->suppliers->name ?? '',
                    'Quantity' => $inventory->quantity . ' ' . ($inventory->units->abbreviation ?? ''),
                    'Landed Cost' => '₱' . number_format($inventory->landed_cost, 2),
                    'Amount' => '₱' . number_format($inventory->amount, 2),
                    'Description' => $inventory->description,
                    'Created At' => $inventory->created_at->format('M d, Y i:s A'),
                ];
            });

        $totalAmount = $purchases->sum(fn($row) => (float) str_replace(['₱', ','], '', $row['Amount']));

        $purchases->push([
            'ID' => '', // Empty cell
            'Transaction Type' => '',
            'Transaction Number' => '',
            'Purchase Date' => '',
            'Category' => '',
            'Subcategory' => '',
            'Supplier Name' => '',
            'Quantity' => '',
            'Landed Cost' => '',
            'Amount' => 'Total Amount: ₱' . number_format($totalAmount, 2), // Total formatted
            'Description' => '', // Label the row
            'Created At' => '', // Empty cell
        ]);

        return $purchases;
    }

    public function headings(): array
    {
        $collection = $this->collection()->where('ID', '!=', '');
        $sales =  $collection->map(function ($item) {
            return $item;
        });
        $fromDate = $this->startDate !== "null" ? $this->startDate : $sales->min('Purchase Date');
        $toDate = $this->endDate !== "null" ? $this->endDate : $sales->max('Purchase Date');

        return [
            ['Lexerl Trading - Purchase In Report'],
            ["From: {$fromDate}"],
            ["To: {$toDate}"],
            [
                'ID',
                'Transaction Type',
                'Transaction Number',
                'Purchase Date',
                'Category',
                'Subcategory',
                'Supplier Name',
                'Quantity',
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
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1:L1')->applyFromArray([
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
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');
        $sheet->getStyle('A2:L3')->applyFromArray([
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
        $sheet->getStyle('A4:L4')->applyFromArray([
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
        $sheet->getStyle("A4:L{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ],
            ],
        ]);

        // Highlight the total row
        $sheet->getStyle("A{$lastRow}:L{$lastRow}")->applyFromArray([
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
