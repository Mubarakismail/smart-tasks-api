<?php

namespace Modules\Task\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Task\Database\Factories\StatusFactory;

class Status extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    public $timestamps = false;
    protected $fillable = ['name', 'code', 'order'];


    protected static function newFactory(): StatusFactory
    {
        return StatusFactory::new();
    }
}
