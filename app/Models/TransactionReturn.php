<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TransactionReturn extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'transaction_returns';

    protected $fillable = [
        'invoice_id',
        'return_no',
        'return_type',
        'return_amount',
        'return_amount_in',
        'reason',
        'created_by'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
