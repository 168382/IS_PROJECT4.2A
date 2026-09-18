<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingLog extends Model
{
    protected $table = 'training_log';

    protected $fillable = [
        'admin_id',
        'records_count',
        'accuracy',
        'model_path',
        'notes',
        'status',
    ];

    protected $casts = [
        'accuracy' => 'decimal:2',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
