<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $guarded = [];
    
    protected $fillable = [
        'product_code',
        'product_name',
        'category_id',
        'brand',
        'purchase_price',
        'selling_price',
        'unit',
        'variant',
        'branch_id',
        'discount',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'category_id', 'category_id');
    }
    
    public function getProfitAttribute()
    {
        // return $this->harga_jual - $this->harga_beli;
        return 0;
    }
    
    public function getProfitPercentageAttribute()
    {
        return 0;
    }

    public function stock()
    {
        return $this->hasOne(ProductStock::class, 'product_id', 'product_id');
    }
}
