<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['developer_id', 'total', 'algorithm_version', 'dimensions', 'calculated_at'])]
class ScoreSnapshot extends Model
{
    use HasFactory, SoftDeletes;

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    protected function casts(): array
    {
        return ['total' => 'float', 'dimensions' => 'array', 'calculated_at' => 'datetime'];
    }
}
