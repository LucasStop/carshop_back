<?php

namespace App\Domain\Users\Entities;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\HasHash;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasHash;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hash',
        'name',
        'photo',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'cpf',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function companiesCreatedByMe(): HasOne
    {
        return $this->hasOne('App\Domain\Companies\Entities\Companies', 'created_by');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(
            'App\Domain\Companies\Entities\Companies',
            'companies_users',
            'user_id',
            'company_id',
        )
            ->wherePivot(
                'status',
                'active'
            )
            ->withPivot('token', 'token_expiration', 'status', 'role_id');
    }

    public function compUser(): HasOne
    {
        return $this->hasOne('App\Domain\Companies\Entities\CompaniesUsers', 'user_id');
    }
}
