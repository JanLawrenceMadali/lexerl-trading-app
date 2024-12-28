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

    public function logUserAction(User $user, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_USERS,
            "{$actor} {$action} user {$user->username}",
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logUnitAction(Unit $unit, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_UNITS,
            "{$actor} {$action} unit {$unit->name}",
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logSupplierAction(Supplier $supplier, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_SUPPLIERS,
            "{$actor} {$action} supplier {$supplier->name}",
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logCustomerAction(Customer $customer, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_CUSTOMERS,
            "{$actor} {$action} customer {$customer->name}",
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logCategoryAction(Category $category, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_CATEGORIES,
            "{$actor} {$action} category {$category->name}",
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logSubCategoryAction(Subcategory $subcategory, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_SUB_CATEGORIES,
            "{$actor} {$action} subcategory {$subcategory->name}",
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logInventoryAction(Inventory $inventory, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_PURCHASES,
            "{$actor} {$action} sale #{$inventory->transaction_number}" . ($inventory->supplier ? " from supplier {$inventory->supplier->name}" : ""),
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logSaleAction(Sale $sale, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_SALES,
            "{$actor} {$action} sale #{$sale->transaction_number}" . ($sale->customer ? " for customer {$sale->customer->name}" : ""),
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logCollectibleAction(Sale $collectible, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $total_amount = number_format($collectible->total_amount, 2);

        $this->log(
            $action,
            ActivityLog::MODULE_COLLECTIBLES,
            "{$actor} {$action} collectible #{$collectible->transaction_number} for customer {$collectible->customer->name} with total amount of {$total_amount}",
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

    public function logPurchaseExport(string $filename, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_PURCHASES,
            "{$actor} exported sales data to {$filename}",
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logSaleExport(string $filename, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_SALES,
            "{$actor} exported sales data to {$filename}",
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logCollectibleExport(string $filename, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_COLLECTIBLES,
            "{$actor} exported collectibles data to {$filename}",
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logActivityLogsExport(string $filename, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_ACTIVITY_LOGS,
            "{$actor} exported activity logs data to {$filename}",
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function logCurrentInventoryExport(string $filename, string $action, array $changes = []): void
    {
        $actor = Auth::user()->username;
        $this->log(
            $action,
            ActivityLog::MODULE_CURRENT_INVENTORY,
            "{$actor} exported current inventory data to {$filename}",
            $changes['old'] ?? null,
            $changes['new'] ?? null
        );
    }

    public function userLogin(User $user, string $action): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_AUTHENTICATION,
            "User {$user->username} logged in"
        );
    }

    public function userLogout(User $user, string $action): void
    {
        $this->log(
            $action,
            ActivityLog::MODULE_AUTHENTICATION,
            "User {$user->username} logged out"
        );
    }
}
