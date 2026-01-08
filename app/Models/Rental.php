<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rental extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'rental_id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rentals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rental_code',
        'rental_product',
        'rental_person',
        'rental_person_phone',
        'rental_person_address',
        'rental_price',
        'rental_duration',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rental_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created the rental.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user who last updated the rental.
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
        return 'rental_code';
    }

    /**
     * Scope a query to search rentals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('rental_code', 'LIKE', "%{$search}%")
                     ->orWhere('rental_product', 'LIKE', "%{$search}%")
                     ->orWhere('rental_person', 'LIKE', "%{$search}%")
                     ->orWhere('rental_person_phone', 'LIKE', "%{$search}%");
    }

    /**
     * Scope a query to filter by active rentals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        // Assuming rental_duration indicates active status
        return $query->whereNotNull('rental_duration');
    }

    /**
     * Calculate the total rental cost.
     *
     * @return float
     */
    public function calculateTotalCost()
    {
        // Example: Parse duration (e.g., "30 days") and calculate
        preg_match('/(\d+)\s*(day|month|week|year)/i', $this->rental_duration, $matches);
        
        if (count($matches) >= 3) {
            $quantity = (int)$matches[1];
            $unit = strtolower($matches[2]);
            
            // Calculate based on unit
            // You can customize this logic as needed
            return $this->rental_price * $quantity;
        }
        
        return $this->rental_price;
    }
}
