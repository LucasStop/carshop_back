<?php

namespace App\Domain\Sales\Entities;

use App\Domain\Cars\Entities\Cars;
use App\Domain\Customers\Entities\Customers;
use App\Domain\Employees\Entities\Employees;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sales extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'car_id',
        'customer_id',
        'employee_id',
        'sale_date',
        'final_price',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sale_date' => 'date',
        'final_price' => 'decimal:2',
    ];

    /**
     * Get the car associated with the sale.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Cars::class, 'car_id');
    }

    /**
     * Get the customer associated with the sale.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    /**
     * Get the employee associated with the sale.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }
}
