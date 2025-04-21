<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Machine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'photo',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($machine) {
            $machine->slug = Str::slug($machine->name);
        });

        static::updating(function ($machine) {
            $machine->slug = Str::slug($machine->name);
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
