<?php

namespace App\Domain\Users\Entities;

use App\Domain\Roles\Entities\Roles;
use App\Domain\Addresses\Entities\Addresses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Users extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'cpf',
        'rg',
        'birth_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
    ];

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Roles::class, 'role_id');
    }
    
    /**
     * Get the user's address.
     */
    public function address(): HasOne
    {
        return $this->hasOne(Addresses::class, 'user_id');
    }
}