<?php

namespace App\Domain\Brands\Entities;

use App\Domain\Models\Entities\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brands extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'country_origin',
    ];

    /**
     * Get the models for the brand.
     */
    public function models(): HasMany
    {
        return $this->hasMany(Models::class, 'brand_id');
    }
}
