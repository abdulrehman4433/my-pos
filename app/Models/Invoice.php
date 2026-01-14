<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'invoice_code',
        'invoice_reference',
        'invoice_resource',
        'invoice_resource_id',
        'sub_total',
        'tax_amount',
        'discount_amount',
        'grand_total',
        'payment_received',
        'payment_status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sub_total' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'grand_total' => 'float',
        'payment_received' => 'string',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
