<?php

namespace App\Domain\Roles\Entities;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    const ADMIN = 1;
    const CLIENT = 2;
    const EMPLOYEE = 3;
}