<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInvoice extends Model
{
    
    use HasFactory;
    protected $table = 'customer_invoice';

    protected $fillable = [
        'invoice_id',
        'customer_id'
    ];

    // public function customers()
    // {
    //     return $this->belongsToMany(Customer::class, 'customer_invoice');
    // }

    // public function invoices()
    // {
    //     return $this->belongsToMany(Invoice::class, 'customer_invoice');
    // }
}
