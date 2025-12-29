<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_company_id',
        'user_id',
        'order_id',
        'amount',
        'payment_method',
        'status',
    ];

    public function maintenanceCompany()
    {
        return $this->belongsTo(MaintenanceCompany::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
