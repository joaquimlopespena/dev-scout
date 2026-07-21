<?php

namespace App\Models;

use Database\Factories\DeveloperFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $last_activity_at
 * @property array<int, string>|null $languages
 */
#[Fillable(['github_id', 'login', 'name', 'avatar_url', 'html_url', 'location', 'bio', 'company', 'primary_language', 'languages', 'followers', 'public_repositories', 'total_stars', 'github_created_at', 'last_activity_at', 'last_synced_at', 'sync_status', 'sync_error'])]
class Developer extends Model
{
    /** @use HasFactory<DeveloperFactory> */
    use HasFactory, SoftDeletes;

    public function scores(): HasMany
    {
        return $this->hasMany(ScoreSnapshot::class);
    }

    public function latestScore(): HasOne
    {
        return $this->hasOne(ScoreSnapshot::class)->latestOfMany('calculated_at');
    }

    protected function casts(): array
    {
        return ['languages' => 'array', 'github_created_at' => 'datetime', 'last_activity_at' => 'datetime', 'last_synced_at' => 'datetime'];
    }
}
