<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoundItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'item_name',
        'description',
        'color',
        'brand',
        'location_found',
        'date_found',
        'image_path',
        'status'
    ];

    protected $casts = [
        'date_found' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
