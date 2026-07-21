<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Favorite;
use App\Models\Note;
use App\Models\PipelineEntry;
use App\Models\Tag;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DeveloperController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Developer::class);

        return Inertia::render('Developers/Index', [
            'discovery' => null,
            'discoveryFilters' => [],
        ]);
    }

    public function show(Request $request, Developer $developer, TenantContext $tenant): Response
    {
        Gate::authorize('view', $developer);
        $organizationId = $tenant->organization()->id;

        return Inertia::render('Developers/Show', [
            'developer' => $developer->load('latestScore'),
            'recruitment' => [
                'favorite' => Favorite::query()->where(['organization_id' => $organizationId, 'developer_id' => $developer->id])->exists(),
                'notes' => Note::query()->where(['organization_id' => $organizationId, 'developer_id' => $developer->id])->latest()->get(),
                'pipeline' => PipelineEntry::query()->where(['organization_id' => $organizationId, 'developer_id' => $developer->id])->value('status'),
                'tags' => Tag::query()->where('organization_id', $organizationId)->whereIn('id', fn ($query) => $query->select('tag_id')->from('developer_tag')->where('developer_id', $developer->id))->pluck('name'),
            ],
        ]);
    }

    public function compare(Request $request): Response
    {
        Gate::authorize('viewAny', Developer::class);
        $ids = collect(explode(',', (string) $request->query('ids')))->filter()->take(4);

        return Inertia::render('Developers/Compare', [
            'developers' => Developer::query()->with('latestScore')->whereIn('id', $ids)->get(),
        ]);
    }
}
