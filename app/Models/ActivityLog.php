<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'user_id',
        'description'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
