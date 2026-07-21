<?php

namespace App\Integrations\GitHub;

use Illuminate\Support\Arr;

class GitHubProfileNormalizer
{
    /** @param array<string,mixed> $profile @param array<int,array<string,mixed>> $repositories @return array<string,mixed> */
    public function normalize(array $profile, array $repositories): array
    {
        $languages = [];
        $stars = 0;
        $lastActivity = null;
        foreach ($repositories as $repository) {
            $language = Arr::get($repository, 'language');
            if (is_string($language)) {
                $languages[$language] = ($languages[$language] ?? 0) + 1;
            }
            $stars += (int) Arr::get($repository, 'stargazers_count', 0);
            $updated = Arr::get($repository, 'pushed_at');
            if (is_string($updated) && ($lastActivity === null || $updated > $lastActivity)) {
                $lastActivity = $updated;
            }
        }
        arsort($languages);

        return ['github_id' => (int) Arr::get($profile, 'id'), 'login' => (string) Arr::get($profile, 'login'), 'name' => Arr::get($profile, 'name'), 'avatar_url' => Arr::get($profile, 'avatar_url'), 'html_url' => (string) Arr::get($profile, 'html_url'), 'location' => Arr::get($profile, 'location'), 'bio' => Arr::get($profile, 'bio'), 'company' => Arr::get($profile, 'company'), 'primary_language' => array_key_first($languages), 'languages' => array_keys($languages), 'followers' => (int) Arr::get($profile, 'followers', 0), 'public_repositories' => (int) Arr::get($profile, 'public_repos', 0), 'total_stars' => $stars, 'github_created_at' => Arr::get($profile, 'created_at'), 'last_activity_at' => $lastActivity, 'last_synced_at' => now(), 'sync_status' => 'synced', 'sync_error' => null];
    }
}
