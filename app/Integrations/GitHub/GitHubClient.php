<?php

namespace App\Integrations\GitHub;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GitHubClient
{
    /** @return array<string, mixed> */
    public function profile(string $login): array
    {
        return Cache::remember('github.profile.'.mb_strtolower($login), now()->addMinutes(15), function () use ($login): array {
            $response = $this->request()->get('/users/'.$login);
            abort_if($response->notFound(), 404, 'Perfil do GitHub não encontrado.');

            return $response->throw()->json();
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function repositories(string $login): array
    {
        return Cache::remember(
            'github.repositories.'.mb_strtolower($login),
            now()->addMinutes(15),
            fn (): array => $this->request()->get('/users/'.$login.'/repos', ['per_page' => 100, 'sort' => 'updated'])->throw()->json(),
        );
    }

    /**
     * @param  array{username?:?string, language?:?string, location?:?string, min_repositories?:?int, min_stars?:?int, sort?:string, page?:int}  $filters
     * @return array<string, mixed>
     */
    public function searchUsers(array $filters): array
    {
        $qualifiers = [
            'type:user',
            'repos:>='.(int) ($filters['min_repositories'] ?? 0),
        ];

        if (! empty($filters['username'])) {
            array_unshift($qualifiers, '"'.$filters['username'].'" in:login');
        }

        if (! empty($filters['language'])) {
            $qualifiers[] = 'language:'.$filters['language'];
        }

        if (! empty($filters['location'])) {
            $qualifiers[] = 'location:"'.str_replace('"', '', $filters['location']).'"';
        }

        $parameters = [
            'q' => implode(' ', $qualifiers),
            'per_page' => 12,
            'page' => (int) ($filters['page'] ?? 1),
            'sort' => $filters['sort'] ?? 'followers',
            'order' => 'desc',
        ];

        return Cache::remember(
            'github.search.'.hash('sha256', http_build_query($parameters)),
            now()->addMinutes(5),
            fn (): array => $this->request()->get('/search/users', $parameters)->throw()->json(),
        );
    }

    private function request(): PendingRequest
    {
        $request = Http::baseUrl('https://api.github.com')
            ->acceptJson()
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->withUserAgent('DevScout')
            ->connectTimeout(3)
            ->timeout(10)
            ->retry(
                3,
                fn (int $attempt, mixed $exception): int => [200, 500, 1000][$attempt - 1] ?? 1000,
                fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response->serverError()),
            );

        $token = config('services.github.token');

        return is_string($token) && $token !== '' ? $request->withToken($token) : $request;
    }
}
