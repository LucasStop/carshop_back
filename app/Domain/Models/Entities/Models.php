<?php

namespace App\Domain\Models\Entities;

use App\Domain\Brands\Entities\Brands;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    /**
     * Get the brand that owns the model.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brands::class, 'brand_id');
    }
}
