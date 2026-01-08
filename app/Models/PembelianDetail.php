<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    protected $table = 'purchase_details';
    protected $primaryKey = 'purchase_detail_id';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'purchase_price',
        'quantity',
        'subtotal',
    ];

    public function product()
    {
        return $this->belongsTo(Produk::class, 'product_id', 'product_id');
    }
    
}
