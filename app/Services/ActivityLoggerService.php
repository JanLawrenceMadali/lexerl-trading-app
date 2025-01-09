<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActivityLoggerService
{
    public function log(
        string $action,
        string $module,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        try {
            $user = Auth::user();

            ActivityLog::create([
                'user_id' => $user->id,
                'role_id' => $user->roles->id,
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'route' => request()->route()->getName(),
                'old_values' => $oldValues,
                'new_values' => $newValues
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create activity log: ' . $e->getMessage());
        }
    }

    public function logUserAction(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_USERS,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logUnitAction(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_UNITS,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logSupplierAction(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_SUPPLIERS,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logCustomerAction(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_CUSTOMERS,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logCategoryAction(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_CATEGORIES,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logSubCategoryAction(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_SUB_CATEGORIES,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logInventoryAction(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_PURCHASES,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logSaleAction(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_SALES,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logCollectibleAction(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_COLLECTIBLES,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logDatabaseBackup(string $action, string $description): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_DATABASE_BACKUP,
            $description
        );
    }

    public function logPurchaseExport(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_PURCHASES,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logSaleExport(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_SALES,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logCollectibleExport(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_COLLECTIBLES,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logActivityLogsExport(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_ACTIVITY_LOGS,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logCurrentInventoryExport(string $action, string $description, array $changes = []): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_CURRENT_INVENTORY,
            $description,
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function userLogin(string $action, string $description): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_AUTHENTICATION,
            $description
        );
    }

    public function userLogout(string $action, string $description): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_AUTHENTICATION,
            $description
        );
    }
}
