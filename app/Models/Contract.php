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
        'company_name',
        'commercial_record_number',
        'head_office',
        'contact_number',
        'service_description',
        'boq_file',
        'drawings_file',
        'other_docs_file',
        'price_offer_file',
        'signed_contract_file',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'project_value' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    protected $appends = [
        'contract_file_url', 
        'contact_numbers_array',
        'boq_file_url',
        'drawings_file_url',
        'other_docs_file_url',
        'price_offer_file_url',
        'signed_contract_file_url',
    ];

    public function getContractFileUrlAttribute()
    {
        if (!$this->contract_file) return null;
        if (filter_var($this->contract_file, FILTER_VALIDATE_URL)) return $this->contract_file;
        return asset($this->contract_file);
    }

    public function getBoqFileUrlAttribute()
    {
        if (!$this->boq_file) return null;
        if (filter_var($this->boq_file, FILTER_VALIDATE_URL)) return $this->boq_file;
        return asset($this->boq_file);
    }

    public function getDrawingsFileUrlAttribute()
    {
        if (!$this->drawings_file) return null;
        if (filter_var($this->drawings_file, FILTER_VALIDATE_URL)) return $this->drawings_file;
        return asset($this->drawings_file);
    }

    public function getOtherDocsFileUrlAttribute()
    {
        if (!$this->other_docs_file) return null;
        if (filter_var($this->other_docs_file, FILTER_VALIDATE_URL)) return $this->other_docs_file;
        return asset($this->other_docs_file);
    }

    public function getPriceOfferFileUrlAttribute()
    {
        if (!$this->price_offer_file) return null;
        if (filter_var($this->price_offer_file, FILTER_VALIDATE_URL)) return $this->price_offer_file;
        return asset($this->price_offer_file);
    }

    public function getSignedContractFileUrlAttribute()
    {
        if (!$this->signed_contract_file) return null;
        if (filter_var($this->signed_contract_file, FILTER_VALIDATE_URL)) return $this->signed_contract_file;
        return asset($this->signed_contract_file);
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
