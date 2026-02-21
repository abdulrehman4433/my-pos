<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    use HasFactory;
    protected $table = 'product_stocks';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_id',
        'stock',
        'minimum_stock',
        'created_by',
        'updated_by'
    ];

    public function product()
    {
        return $this->belongsTo(Produk::class, 'product_id', 'product_id');
    }
}
