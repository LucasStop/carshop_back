<?php

namespace App\Domain\Employees\Entities;

use App\Domain\Sales\Entities\Sales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employees extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'position',
        'email',
        'phone',
        'cpf',
        'rg',
        'birth_date',
        'address',
        'number',
        'complement',
        'city',
        'state',
        'zip_code',
        'hire_date',
        'salary',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'salary' => 'decimal:2',
    ];

    /**
     * Get the sales for the employee.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class, 'employee_id');
    }
}
