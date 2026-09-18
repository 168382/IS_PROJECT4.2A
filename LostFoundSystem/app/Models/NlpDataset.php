<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NlpDataset extends Model
{
    protected $table = 'nlp_dataset';

    protected $fillable = [
        'item_name',
        'description',
        'category_name',
        'color',
        'brand',
        'location',
        'item_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
