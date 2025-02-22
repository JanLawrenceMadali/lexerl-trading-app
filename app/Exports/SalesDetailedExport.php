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
    protected ?string $startDate;
    protected ?string $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $this->parseDate($startDate);
        $this->endDate = $this->parseDate($endDate);
    }

    private function parseDate($date): ?string
    {
        return $date !== "null" ? Carbon::parse($date)->format('Y-m-d') : null;
    }

    public function collection()
    {
        $sales = $this->fetchSales();
        $units = Unit::all()->keyBy('id');

        $salesData = $sales->flatMap(fn($sale) => $this->transformSale($sale, $units))->values();
        $this->appendTotalAmountRow($salesData, $sales);

        return $salesData;
    }

    private function fetchSales()
    {
        $query = Sale::with([
            'inventory_sale.categories',
            'inventory_sale.subcategories',
            'statuses',
            'customers',
            'transactions'
        ])
            ->select('sales.*')
            ->join('inventory_sale', 'sales.id', '=', 'inventory_sale.sale_id')
            ->groupBy([
                'inventory_sale.sale_id',
                'inventory_sale.unit_id',
                'inventory_sale.category_id',
                'inventory_sale.subcategory_id'
            ]);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('sale_date', [$this->startDate, $this->endDate]);
        }

        return $query->get();
    }

    private function transformSale($sale, $units)
    {
        $totals = $this->calculateTotals($sale);

        return $sale->inventory_sale->groupBy(fn($item) => $this->generateKey($item->pivot))
            ->map(fn($group) => $this->mapSaleGroup($group, $sale, $units, $totals));
    }

    private function calculateTotals($sale)
    {
        return $sale->inventory_sale->groupBy(fn($item) => $this->generateKey($item->pivot))
            ->map(fn($group) => [
                'total_quantity' => $group->sum('pivot.quantity'),
                'total_amount' => $group->sum('pivot.amount')
            ]);
    }

    private function generateKey($pivot): string
    {
        return implode('-', [
            $pivot->sale_id,
            $pivot->unit_id,
            $pivot->category_id,
            $pivot->subcategory_id
        ]);
    }

    private function mapSaleGroup($group, $sale, $units, $totals)
    {
        $product = $group->first();
        $identifier = $this->generateKey($product->pivot);
        $total = $totals[$identifier] ?? ['total_quantity' => 0, 'total_amount' => 0];
        $unit = $units[$product->pivot->unit_id] ?? null;

        return [
            'ID' => $sale->id,
            'Transaction Type' => $sale->transactions->type ?? '',
            'Transaction Number' => $sale->transaction_number,
            'Sale Date' => $sale->sale_date,
            'Customer Name' => $sale->customers->name ?? '',
            'Category' => $product->categories->name ?? '',
            'Subcategory' => $product->subcategories->name ?? '',
            'Quantity' => $total['total_quantity'],
            'UM' => $unit->abbreviation ?? '',
            'Selling Price' => '₱' . number_format($product->pivot->selling_price, 2),
            'Amount' => '₱' . number_format($total['total_amount'], 2),
            'Status' => $sale->statuses->name ?? '',
            'Due Date' => $sale->dues->days ?? '',
            'Description' => $sale->description,
            'Created At' => $sale->created_at->format('Y-m-d h:i A'),
        ];
    }

    private function appendTotalAmountRow(&$salesData, $sales)
    {
        $totalAmount = $sales->flatMap(fn($sale) => $sale->inventory_sale
            ->groupBy(fn($item) => $this->generateKey($item->pivot))
            ->map(fn($group) => $group->sum('pivot.amount')))
            ->sum();

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
    }

    public function headings(): array
    {
        $sale_date = Sale::get();
        return [
            ['Lexerl Trading - Sales Detailed Report'],
            ["From: " . ($this->startDate ?? $sale_date->min('sale_date'))],
            ["To: " . ($this->endDate ?? $sale_date->max('sale_date'))],
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
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1:O1')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'font' => ['bold' => true, 'size' => 18],
        ]);

        $sheet->mergeCells('A2:O2');
        $sheet->mergeCells('A3:O3');
        $sheet->getStyle('A2:O3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'font' => ['italic' => true, 'size' => 12],
        ]);

        $sheet->getStyle('A4:O4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
        ]);

        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A4:O{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $sheet->getStyle("A{$lastRow}:O{$lastRow}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF99']],
            'font' => ['bold' => true],
        ]);

        return [
            4   => ['font' => ['bold' => true, 'size' => 12]],
            'A' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}
