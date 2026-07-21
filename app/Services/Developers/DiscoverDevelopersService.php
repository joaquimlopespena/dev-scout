<?php

namespace App\Services\Developers;

use App\Integrations\GitHub\GitHubClient;
use App\Models\Developer;
use App\Models\PipelineEntry;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Arr;

class DiscoverDevelopersService
{
    public function __construct(
        private GitHubClient $github,
        private TenantContext $tenant,
    ) {}

    /**
     * @param  array{username?:?string, language?:?string, location?:?string, min_repositories?:?int, min_stars?:?int, sort?:string, page?:int}  $filters
     * @return array{data:array<int, array<string, mixed>>, total:int, page:int, last_page:int, previous_page_url:?string, next_page_url:?string}
     */
    public function search(array $filters): array
    {
        $result = $this->github->searchUsers($filters);
        $minimumStars = (int) ($filters['min_stars'] ?? 0);
        $items = collect($result['items'] ?? [])->map(function (array $item): array {
            $repositories = $this->github->repositories((string) $item['login']);
            $item['total_stars'] = collect($repositories)->sum(
                fn (array $repository): int => (int) Arr::get($repository, 'stargazers_count', 0),
            );

            return $item;
        })->filter(fn (array $item): bool => $item['total_stars'] >= $minimumStars)->values();
        $developers = Developer::query()->whereIn('login', $items->pluck('login'))->pluck('id', 'login');
        $selectedDeveloperIds = PipelineEntry::query()
            ->where('organization_id', $this->tenant->organization()->id)
            ->whereIn('developer_id', $developers->values())
            ->pluck('developer_id');
        $page = (int) ($filters['page'] ?? 1);
        $total = min((int) ($result['total_count'] ?? 0), 1000);
        $lastPage = max(1, (int) ceil($total / 12));
        $query = array_filter($filters, fn (mixed $value): bool => $value !== null && $value !== '');
        unset($query['page']);

        return [
            'data' => $items->map(function (array $item) use ($developers, $selectedDeveloperIds): array {
                $developerId = $developers[$item['login']] ?? null;

                return [
                    'login' => $item['login'],
                    'avatar_url' => $item['avatar_url'],
                    'html_url' => $item['html_url'],
                    'total_stars' => $item['total_stars'],
                    'selected' => $developerId !== null && $selectedDeveloperIds->contains($developerId),
                ];
            })->all(),
            'total' => $total,
            'page' => $page,
            'last_page' => $lastPage,
            'previous_page_url' => $page > 1 ? route('developers.discover', [...$query, 'page' => $page - 1], false) : null,
            'next_page_url' => $page < $lastPage ? route('developers.discover', [...$query, 'page' => $page + 1], false) : null,
        ];
    }
}
