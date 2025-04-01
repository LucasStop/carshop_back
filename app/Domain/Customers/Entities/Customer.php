<?php

namespace App\Domain\Customers\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'cpf',
        'birth_date',
        'address',
        'number',
        'complement',
        'city',
        'state',
        'zip_code'
    ];

    protected $hidden = [
        'deleted_at',
    ];
}
