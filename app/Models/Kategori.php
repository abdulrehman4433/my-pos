<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';
    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    /**
     * Relationship with Produk
     */
    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Get active products count
     */
    public function getActiveProdukCountAttribute()
    {
        return $this->produk()->where('status', true)->count();
    }
}
