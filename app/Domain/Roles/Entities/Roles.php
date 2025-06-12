<?php

namespace App\Domain\Roles\Entities;

use App\Domain\Users\Entities\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roles extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'permissions' => 'array',
    ];
    /**
     * Get the users that have this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(Users::class, 'role_id');
    }
}
