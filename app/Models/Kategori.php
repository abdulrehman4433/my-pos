<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey = 'category_id';
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
        return $this->hasMany(Produk::class, 'category_id', 'category_id');
    }

    /**
     * Get active products count
     */
    public function getActiveProdukCountAttribute()
    {
        return $this->produk()->where('status', true)->count();
    }
}
