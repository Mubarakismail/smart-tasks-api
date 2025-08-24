<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// use Modules\Core\Database\Factories\AuditFactory;

class Audit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['auditable_type', 'auditable_id', 'user_id', 'event', 'old_values', 'new_values'];

    protected $casts = ['old_values' => 'array', 'new_values' => 'array'];

    // protected static function newFactory(): AuditFactory
    // {
    //     // return AuditFactory::new();
    // }
}
