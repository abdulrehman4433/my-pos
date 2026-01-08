<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'maintenance_id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'maintenances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'maintenance_code',
        'maintenance_name',
        'maintenance_address',
        'maintenance_phone',
        'maintenance_price',
        'maintenance_duration',
        'maintenance_details',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'maintenance_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created the maintenance.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user who last updated the maintenance.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'maintenance_code';
    }

    /**
     * Scope a query to search maintenances.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('maintenance_code', 'LIKE', "%{$search}%")
                     ->orWhere('maintenance_name', 'LIKE', "%{$search}%")
                     ->orWhere('maintenance_phone', 'LIKE', "%{$search}%");
    }

    /**
     * Scope a query to filter by maintenance type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('maintenance_name', 'LIKE', "%{$type}%");
    }

    /**
     * Scope a query to filter by active maintenance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        // Assuming maintenance_duration indicates active status
        return $query->whereNotNull('maintenance_duration');
    }

    /**
     * Get a formatted price with currency.
     *
     * @return string
     */
    public function getFormattedPriceAttribute()
    {
        return '₹' . number_format($this->maintenance_price, 2);
    }

    /**
     * Get a short preview of maintenance details.
     *
     * @param  int  $length
     * @return string
     */
    public function getDetailsPreview($length = 100)
    {
        if (strlen($this->maintenance_details) <= $length) {
            return $this->maintenance_details;
        }
        
        return substr($this->maintenance_details, 0, $length) . '...';
    }
}
