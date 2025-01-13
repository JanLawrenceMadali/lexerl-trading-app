<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\ActivityLog;
use App\Http\Requests\UnitRequest;
use App\Services\UnitService;
use App\Services\ActivityLoggerService;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    protected $unitService;
    protected $activityLog;
    private $actor;

    public function __construct(
        UnitService $unitService,
        ActivityLoggerService $activityLoggerService
    ) {
        $this->unitService = $unitService;
        $this->activityLog = $activityLoggerService;
        $this->actor = Auth::user()->username;
    }

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
            $unit = $this->unitService->createUnit($validated);

            $this->activityLog->logUnitAction(
                ActivityLog::ACTION_CREATED,
                "{$this->actor} created a new unit: {$unit->name}",
                ['new' => $unit->toArray()]
            );

            return redirect()->back()->with('success', 'Unit created successfully!');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create a unit');
        }
    }

    public function update(UnitRequest $unitRequest, Unit $unit)
    {
        try {
            $oldData = $unit->toArray();

            $this->unitService->updateUnit($unit, $unitRequest->validated());

            $this->activityLog->logUnitAction(
                ActivityLog::ACTION_UPDATED,
                "{$this->actor} updated a unit: {$unit->name}",
                ['old' => $oldData, 'new' => $unit->toArray()]
            );

            return redirect()->back()->with('success', 'Unit updated successfully!');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to update unit');
        }
    }

    public function destroy(Unit $unit)
    {
        try {
            $this->unitService->deleteUnit($unit);

            $this->activityLog->logUnitAction(
                ActivityLog::ACTION_DELETED,
                "{$this->actor} deleted a unit: {$unit->name}",
                ['old' => $unit->toArray()]
            );

            return redirect()->back()->with('success', 'Unit deleted successfully!');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to delete unit');
        }
    }
}
