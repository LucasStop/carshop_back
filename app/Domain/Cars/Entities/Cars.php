<?php

namespace App\Domain\Cars\Entities;

use App\Domain\Models\Entities\Models;
use App\Domain\Sales\Entities\Sales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cars extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */    protected $fillable = [
        'model_id',
        'vin',
        'color',
        'manufacture_year',
        'mileage',
        'price',
        'status',
        'inclusion_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */    protected $casts = [
        'manufacture_year' => 'integer',
        'mileage' => 'integer',
        'price' => 'decimal:2',
        'inclusion_date' => 'date',
    ];

    /**
     * Get the model that owns the car.
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(Models::class, 'model_id');
    }

    /**
     * Get the sales for the car.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class, 'car_id');
    }
}
