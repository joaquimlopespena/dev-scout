<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['organization_id', 'developer_id', 'status', 'updated_by'])]
class PipelineEntry extends Model
{
    use HasFactory, SoftDeletes;

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }
}
