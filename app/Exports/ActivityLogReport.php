<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ActivityLogReport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    public function query()
    {
        return ActivityLog::query()
            ->with(['users:id,username']);
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->users->username,
            $row->action,
            $row->description,
            $row->created_at->format('m/d/Y')
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Action',
            'Description',
            'Created At',
        ];
    }
}
