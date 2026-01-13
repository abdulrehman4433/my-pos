<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Project extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'projects';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'project_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_code',
        'project_name',
        'project_address',
        'project_phone',
        'project_price',
        'project_duration',
        'project_details',
        'project_status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'project_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created the project.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user who last updated the project.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Use project_code for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'project_code';
    }

    /**
     * Scope: search projects.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('project_code', 'LIKE', "%{$search}%")
              ->orWhere('project_name', 'LIKE', "%{$search}%")
              ->orWhere('project_phone', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope: filter by price range.
     */
    public function scopePriceRange($query, float $min = 0, float $max = null)
    {
        if ($max !== null) {
            return $query->whereBetween('project_price', [$min, $max]);
        }

        return $query->where('project_price', '>=', $min);
    }
}