<?php

namespace App\Exports;

use App\Models\Sale;
use Carbon\Carbon;
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
        $this->startDate = $startDate !== "null" ? Carbon::parse($startDate)->format('Y-m-d') : null;
        $this->endDate = $endDate !== "null" ? Carbon::parse($endDate)->format('Y-m-d') : null;
    }

    public function collection()
    {
        $query = Sale::has('products')
            ->with('products.categories', 'products.subcategories', 'statuses', 'customers', 'transactions')
            ->where('status_id', 2);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('sale_date', [$this->startDate, $this->endDate]);
        }

        $collectibles = $query->get();

        $collectiblesData = $collectibles->map(function ($sale) {
            return [
                'ID' => $sale->id,
                'Transaction Type' => $sale->transactions->type ?? '',
                'Transaction Number' => $sale->transaction_number,
                'Sale Date' => $sale->sale_date,
                'Customer Name' => $sale->customers->name ?? '',
                'Total Amount' => '₱' . number_format($sale->total_amount, 2),
                'Status' => $sale->statuses->name ?? '',
                'Due Date' => $sale->dues->days ?? '',
                'Description' => $sale->description,
                'Created At' => $sale->created_at->format('Y-m-d h:i A'),
            ];
        });

        $totalAmount = $collectiblesData->sum(fn($row) => (float) str_replace(['₱', ','], '', $row['Total Amount']));

        $collectiblesData->push([
            'ID' => '',
            'Transaction Type' => '',
            'Transaction Number' => '',
            'Sale Date' => '',
            'Customer Name' => '',
            'Total Amount' => 'Total: ₱' . number_format($totalAmount, 2),
            'Status' => '',
            'Due Date' => '',
            'Description' => '',
            'Created At' => '',
        ]);

        return $collectiblesData;
    }

    public function headings(): array
    {
        return [
            ['Lexerl Trading Inc - Collectibles Report'],
            ["From: " . ($this->startDate ?? 'All Time')],
            ["To: " . ($this->endDate ?? 'All Time')],
            [
                'ID',
                'Transaction Type',
                'Transaction Number',
                'Sale Date',
                'Customer Name',
                'Total Amount',
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
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1:J1')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['bold' => true, 'size' => 18],
        ]);

        // Style date range rows
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');
        $sheet->getStyle('A2:J3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'font' => ['italic' => true, 'size' => 12],
        ]);

        // Style header row
        $sheet->getStyle('A4:J4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
        ]);

        // Style all data rows and add borders
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A4:J{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Highlight the total row
        $sheet->getStyle("A{$lastRow}:J{$lastRow}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF99']],
            'font' => ['bold' => true],
        ]);

        return [
            4   => ['font' => ['bold' => true, 'size' => 12]], // Headings row
            'A' => ['alignment' => ['horizontal' => 'center']], // Center align column A
        ];
    }
}
