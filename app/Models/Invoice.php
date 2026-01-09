<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'invoice_reference',
        'reference_id',
        'sub_total',
        'tax_amount',
        'discount_amount',
        'grand_total',
        'payment_received',
        'payment_status',
        'created_by',
        'updated_by',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
