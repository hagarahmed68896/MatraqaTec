<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_number',
        'maintenance_company_id',
        'corporate_customer_id',
        'user_id',
        'contract_file',
        'project_value',
        'paid_amount',
        'remaining_amount',
        'contact_numbers',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $appends = ['contract_file_url', 'contact_numbers_array'];

    public function getContractFileUrlAttribute()
    {
        if (!$this->contract_file) return null;
        if (filter_var($this->contract_file, FILTER_VALIDATE_URL)) return $this->contract_file;
        return asset($this->contract_file);
    }

    public function getContactNumbersArrayAttribute()
    {
        if (!$this->contact_numbers) return [];
        return is_array($this->contact_numbers) 
            ? $this->contact_numbers 
            : explode(',', $this->contact_numbers);
    }

    public function maintenanceCompany()
    {
        return $this->belongsTo(MaintenanceCompany::class);
    }

    public function paymentReceipts()
    {
        return $this->hasMany(ContractPaymentReceipt::class);
    }

    public function corporateCustomer()
    {
        return $this->belongsTo(CorporateCustomer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
