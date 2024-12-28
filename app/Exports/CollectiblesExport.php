<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CollectiblesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
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
        $sales = Sale::has('products')
            ->with('products.categories', 'products.subcategories', 'statuses', 'customers', 'transactions')
            ->where('status_id', 2)
            ->when($this->startDate !== "null" && $this->endDate !== "null", function ($query) {
                $query->whereBetween('sale_date', [$this->startDate, $this->endDate]);
            })
            ->get();

        $units = Unit::all()->keyBy('id');

        $data = $sales->flatMap(function ($sale) use ($units) {
            return $sale->products->map(function ($product) use ($sale, $units) {
                $unit = $units[$product->pivot->unit_id] ?? null;
                return [
                    'ID' => $sale->id,
                    'Transaction Type' => $sale->transactions->type ?? '',
                    'Transaction Number' => $sale->transaction_number,
                    'Sale Date' => $sale->sale_date,
                    'Customer Name' => $sale->customers->name ?? '',
                    'Category' => $product->categories->name ?? '',
                    'Subcategory' => $product->subcategories->name ?? '',
                    'Quantity' => $product->pivot->quantity . ($unit ? " {$unit->abbreviation}" : ''),
                    'Selling Price' => '₱' . number_format($product->pivot->selling_price, 2),
                    'Amount' => '₱' . number_format($sale->total_amount, 2),
                    'Status' => $sale->statuses->name ?? '',
                    'Due Date' => $sale->dues->days ?? '',
                    'Description' => $sale->description,
                    'Created At' => $sale->created_at->format('M d, Y i:s A'),
                ];
            });
        });

        $totalAmount = $sales->sum(function ($sale) {
            return $sale->products->sum('pivot.amount');
        });

        $data->push([
            'ID'                => '',
            'Transaction Type'  => '',
            'Transaction Number' => '',
            'Sale Date'         => '',
            'Customer Name'     => '',
            'Category'          => '',
            'Subcategory'       => '',
            'Quantity'          => '',
            'Selling Price'     => '',
            'Amount'            => 'Total Amount: ₱' . number_format($totalAmount, 2),
            'Status'            => '',
            'Due Date'          => '',
            'Description'       => '',
            'Created At'        => '',
        ]);

        return $data;
    }

    public function headings(): array
    {
        $collection = $this->collection()->where('ID', '!=', '');
        $sales =  $collection->map(function ($item) {
            return $item;
        });
        $fromDate = $this->startDate !== "null" ? $this->startDate : $sales->min('Sale Date');
        $toDate = $this->endDate !== "null" ? $this->endDate : $sales->max('Sale Date');

        return [
            ['Lexerl Trading - Collectibles Report'],
            ["From: {$fromDate}"],
            ["To: {$toDate}"],
            [
                'ID',
                'Transaction Type',
                'Transaction Number',
                'Sale Date',
                'Customer Name',
                'Category',
                'Subcategory',
                'Quantity',
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
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1:N1')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['bold' => true, 'size' => 18],
        ]);

        $sheet->mergeCells('A2:N2');
        $sheet->mergeCells('A3:N3');
        $sheet->getStyle('A2:N3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['italic' => true, 'size' => 12],
        ]);

        $sheet->getStyle('A4:N4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
        ]);

        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A4:N{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $sheet->getStyle("A{$lastRow}:N{$lastRow}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF99']],
            'font' => ['bold' => true],
        ]);

        return [
            4   => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
