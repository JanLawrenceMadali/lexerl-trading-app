<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\Unit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesDetailedExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
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
        $query = Sale::whereHas('inventory_sale')
            ->with('inventory_sale.categories', 'inventory_sale.subcategories', 'statuses', 'customers', 'transactions');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('sale_date', [$this->startDate, $this->endDate]);
        }

        $sales = $query->get();

        $units = Unit::all()->keyBy('id');

        $salesData = $sales->flatMap(function ($sale) use ($units) {
            return $sale->inventory_sale->map(function ($product) use ($sale, $units) {
                $unit = $units[$product->unit_id] ?? null;
                
                return [
                    'ID' => $sale->id,
                    'Transaction Type' => $sale->transactions->type ?? '',
                    'Transaction Number' => $sale->transaction_number,
                    'Sale Date' => $sale->sale_date,
                    'Customer Name' => $sale->customers->name ?? '',
                    'Category' => $product->categories->name ?? '',
                    'Subcategory' => $product->subcategories->name ?? '',
                    'Quantity' => $product->pivot->quantity,
                    'UM' => $unit->abbreviation ?? '',
                    'Selling Price' => '₱' . number_format($product->pivot->selling_price, 2),
                    'Amount' => '₱' . number_format($product->pivot->amount, 2),
                    'Status' => $sale->statuses->name ?? '',
                    'Due Date' => $sale->dues->days ?? '',
                    'Description' => $sale->description,
                    'Created At' => $sale->created_at->format('Y-m-d h:i A'),
                ];
            });
        });

        $totalAmount = $sales->sum(function ($sale) {
            return $sale->inventory_sale->sum('pivot.amount');
        });

        $salesData->push([
            'ID' => '',
            'Transaction Type' => '',
            'Transaction Number' => '',
            'Sale Date' => '',
            'Customer Name' => '',
            'Category' => '',
            'Subcategory' => '',
            'Quantity' => '',
            'UM' => '',
            'Selling Price' => '',
            'Amount' => 'Total: ₱' . number_format($totalAmount, 2),
            'Status' => '',
            'Due Date' => '',
            'Description' => '',
            'Created At' => '',
        ]);

        return $salesData;
    }

    public function headings(): array
    {
        return [
            ['Lexerl Trading - Sales Detailed Report'],
            ["From: " . ($this->startDate ?? 'All Time')],
            ["To: " . ($this->endDate ?? 'All Time')],
            [
                'ID',
                'Transaction Type',
                'Transaction Number',
                'Sale Date',
                'Customer Name',
                'Category',
                'Subcategory',
                'Quantity',
                'UM',
                'Selling Price',
                'Amount',
                'Status',
                'Due Date',
                'Description',
                'Created At',
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge title and style it
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1:O1')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['bold' => true, 'size' => 18],
        ]);

        // Style date range rows
        $sheet->mergeCells('A2:O2');
        $sheet->mergeCells('A3:O3');
        $sheet->getStyle('A2:O3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['italic' => true, 'size' => 12],
        ]);

        // Style header row
        $sheet->getStyle('A4:O4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
        ]);

        // Style all data rows and add borders
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A4:O{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Highlight the total row
        $sheet->getStyle("A{$lastRow}:O{$lastRow}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF99']],
            'font' => ['bold' => true],
        ]);

        return [
            4   => ['font' => ['bold' => true, 'size' => 12]], // Headings row
            'A' => ['alignment' => ['horizontal' => 'center']], // Center align column A
        ];
    }
}
