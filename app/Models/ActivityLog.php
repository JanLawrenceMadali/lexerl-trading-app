<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_id',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'route',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    const ACTION_CREATED = 'create';
    const ACTION_UPDATED = 'update';
    const ACTION_DELETED = 'delete';
    const ACTION_CANCELLED = 'cancel';
    const ACTION_PAID = 'paid';
    const ACTION_BACKUP = 'backup';
    const ACTION_PURGE = 'purge';
    const ACTION_DOWNLOAD = 'download';
    const ACTION_RESTORE = 'restore';
    const ACTION_EXPORTED = 'export';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';

    const MODULE_PURCHASES = 'purchase-in';
    const MODULE_SALES = 'sales';
    const MODULE_COLLECTIBLES = 'collectibles';
    const MODULE_CURRENT_INVENTORY = 'current inventory';
    const MODULE_ACTIVITY_LOGS = 'activity logs';
    const MODULE_USERS = 'manage users';
    const MODULE_UNITS = 'manage units';
    const MODULE_SUPPLIERS = 'manage suppliers';
    const MODULE_CUSTOMERS = 'manage customers';
    const MODULE_CATEGORIES = 'manage categories';
    const MODULE_SUB_CATEGORIES = 'manage sub categories';
    const MODULE_DATABASE_BACKUP = 'manage database backup';
    const MODULE_AUTHENTICATION = 'authentication';

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function roles(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function scopeForRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }
}
