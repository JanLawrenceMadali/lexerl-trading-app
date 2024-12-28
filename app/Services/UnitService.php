<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnitService
{
    public function createUnit(array $data)
    {
        return DB::transaction(function () use ($data) {

            $unit = Unit::create([
                'name' => $data['name'],
                'abbreviation' => $data['abbreviation'],
            ]);

            return $unit;
        });
    }

    public function updateUnit(Unit $unit, array $data)
    {
        return DB::transaction(function () use ($unit, $data) {

            $unit->update([
                'name' => $data['name'],
                'abbreviation' => $data['abbreviation'],
            ]);

            return $unit;
        });
    }

    public function deleteUnit(Unit $unit)
    {
        return DB::transaction(function () use ($unit) {

            $unit->delete();

            return true;
        });
    }
}
