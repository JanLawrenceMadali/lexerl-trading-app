<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\ActivityLog;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::latest()->get();
        return inertia('Settings/Units/Index', [
            'units' => $units
        ]);
    }

    public function store(UnitRequest $unitRequest)
    {
        $validated = $unitRequest->validated();

        try {
            DB::transaction(function () use ($validated) {
                Unit::create($validated);

                $this->logs('Unit ' . $validated['name'] . ' was created');
            });

            return redirect()->route('units')->with('success', 'Unit created successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('units')->with('error', 'Something went wrong');
        }
    }

    public function update(UnitRequest $unitRequest, Unit $unit)
    {
        $validated = $unitRequest->validated();

        try {
            DB::transaction(function () use ($validated, $unit) {
                $unit->update($validated);

                $this->logs('Unit ' . $validated['name'] . ' was updated');
            });

            return redirect()->route('units')->with('success', 'Unit updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('units')->with('error', 'Something went wrong');
        }
    }

    public function destroy(Unit $unit)
    {
        try {
            DB::transaction(function () use ($unit) {
                $unit->delete();

                $this->logs('Unit ' . $unit->name . ' was deleted');
            });

            return redirect()->route('units')->with('success', 'Unit deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('units')->with('error', 'Something went wrong');
        }
    }

    private function logs(string $action)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $action . ' by ' . Auth::user()->username,
        ]);
    }
}