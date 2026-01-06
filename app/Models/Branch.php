<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

        protected $fillable = [
        'name',
        'code',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Produk::class);
    }

    public function sales()
    {
        return $this->hasMany(Penjualan::class);
    }

    public function purchases()
    {
        return $this->hasMany(Pembelian::class);
    }

    public function expenses()
    {
        return $this->hasMany(Pengeluaran::class);
    }
    public function categories()
    {
        return $this->hasMany(Kategori::class, 'branch_id');
    }

}
