<?php

namespace App\Domain\Models\Entities;

use App\Domain\Brands\Entities\Brands;
use App\Domain\Cars\Entities\Cars;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Models extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'brand_id',
        'name',
        'year_model',
        'engine',
        'power',
        'base_price',
        'quantity'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year_model' => 'integer',
        'power' => 'integer',
        'base_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the brand that owns the model.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brands::class, 'brand_id');
    }

    /**
     * Get the cars for the model.
     */
    public function cars(): HasMany
    {
        return $this->hasMany(Cars::class, 'model_id');
    }
}
