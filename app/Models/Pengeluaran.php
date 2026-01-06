<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran';
    protected $primaryKey = 'id_pengeluaran';
    
    protected $fillable = [
        'deskripsi',
        'nominal',
        'branch_id',
        'created_at',
        'updated_at'
    ];
    
    // Or if you prefer guarded but want to allow mass assignment for specific fields
    // protected $guarded = ['id_pengeluaran'];
    
    protected $casts = [
        'nominal' => 'integer',
        'branch_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}