<?php

namespace App\Repositories\Developer;

use App\Models\Developer;
use App\Models\Favorite;
use App\Models\PipelineEntry;
use App\Models\ScoreSnapshot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentDeveloperRepository implements DeveloperRepository
{
    public function paginate(array $filters, int $organizationId): LengthAwarePaginator
    {
        return Developer::query()->with('latestScore')->select('developers.*')
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where(fn ($q) => $q->where('login', 'ilike', '%'.$v.'%')->orWhere('name', 'ilike', '%'.$v.'%')))
            ->when($filters['location'] ?? null, fn ($q, $v) => $q->where('location', 'ilike', '%'.$v.'%'))
            ->when($filters['language'] ?? null, fn ($q, $v) => $q->where('primary_language', $v))
            ->when($filters['favorite'] ?? null, fn ($q) => $q->whereIn('id', Favorite::query()->where('organization_id', $organizationId)->select('developer_id')))
            ->when($filters['pipeline'] ?? null, fn ($q, $v) => $q->whereIn('id', PipelineEntry::query()->where('organization_id', $organizationId)->where('status', $v)->select('developer_id')))
            ->when($filters['min_score'] ?? null, fn ($q, $v) => $q->whereHas('latestScore', fn ($q) => $q->where('total', '>=', $v)))
            ->orderByDesc(ScoreSnapshot::query()->select('total')->whereColumn('developer_id', 'developers.id')->latest('calculated_at')->limit(1))
            ->paginate(18)->withQueryString();
    }

    public function upsertFromGitHub(array $attributes): Developer
    {
        return Developer::query()->updateOrCreate(['github_id' => $attributes['github_id']],$attributes);
    }
}
