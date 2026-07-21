<?php

namespace App\Services\Developers;

use App\Integrations\GitHub\GitHubClient;
use App\Integrations\GitHub\GitHubProfileNormalizer;
use App\Models\Developer;
use App\Repositories\Developer\DeveloperRepository;
use App\Services\Scoring\DeveloperScoringService;

class SyncDeveloperService
{
    public function __construct(private GitHubClient $github, private GitHubProfileNormalizer $normalizer, private DeveloperRepository $developers, private DeveloperScoringService $scoring) {}

    public function sync(string $login): Developer
    {
        $profile = $this->github->profile($login);
        $repositories = $this->github->repositories($login);
        $developer = $this->developers->upsertFromGitHub($this->normalizer->normalize($profile, $repositories));
        $this->scoring->calculate($developer);

        return $developer->refresh()->load('latestScore');
    }
}
