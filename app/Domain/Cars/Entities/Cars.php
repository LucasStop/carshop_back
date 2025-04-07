<?php

namespace App\Domain\Cars\Entities;

use App\Domain\Models\Entities\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cars extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'model_id',
        'vin',
        'color',
        'manufacture_year',
        'mileage',
        'status',
        'inclusion_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'manufacture_year' => 'integer',
        'mileage' => 'integer',
        'inclusion_date' => 'date',
    ];

    /**
     * Get the model that owns the car.
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(Models::class, 'model_id');
    }
}
