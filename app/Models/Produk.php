<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
    
    /**
     * Calculate profit
     */
    public function getProfitAttribute()
    {
        return $this->harga_jual - $this->harga_beli;
    }

    /**
     * Calculate profit percentage
     */
    public function getProfitPercentageAttribute()
    {
        if ($this->harga_beli > 0) {
            return ($this->profit / $this->harga_beli) * 100;
        }
        return 0;
    }
}
