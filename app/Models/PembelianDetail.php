<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_details';
    protected $primaryKey = 'purchase_detail_id';
    protected $guarded = [];

    public function produk()
    {
        return $this->hasOne(Produk::class, 'product_id', 'product_id');
    }
}
