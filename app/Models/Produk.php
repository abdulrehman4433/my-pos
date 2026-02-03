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
        // if ($this->harga_beli > 0) {
        //     return ($this->profit / $this->harga_beli) * 100;
        // }
        return 0;
    }

    public function stock()
    {
        return $this->hasOne(ProductStock::class, 'product_id', 'product_id');
    }
}
