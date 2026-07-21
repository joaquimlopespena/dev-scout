<?php

namespace App\Services\Scoring;

use App\Models\Developer;
use App\Models\ScoreSnapshot;

class DeveloperScoringService
{
    public const VERSION = '1.0.0';

    /** @var array<string, float> */
    private array $weights = [
        'technical_impact' => .25,
        'contribution_quality' => .25,
        'consistency' => .20,
        'technical_depth' => .15,
        'collaboration' => .10,
        'profile_completeness' => .05,
    ];

    public function calculate(Developer $developer): ScoreSnapshot
    {
        $values = [
            'technical_impact' => $this->cap($developer->total_stars, 500),
            'contribution_quality' => $this->cap($developer->total_stars + $developer->public_repositories * 2, 400),
            'consistency' => $developer->last_activity_at ? max(0, 100 - min(100, $developer->last_activity_at->diffInDays(now()))) : null,
            'technical_depth' => $developer->primary_language ? min(100, 40 + count($developer->languages ?? []) * 10) : null,
            'collaboration' => $this->cap($developer->followers, 500),
            'profile_completeness' => collect([$developer->name, $developer->bio, $developer->location, $developer->company, $developer->avatar_url])->filter()->count() * 20.0,
        ];
        $availableWeight = 0.0;
        $weighted = 0.0;
        $dimensions = [];

        foreach ($this->weights as $name => $weight) {
            $value = $values[$name];
            if ($value !== null) {
                $availableWeight += $weight;
                $weighted += $value * $weight;
            }
            $dimensions[$name] = ['value' => $value, 'weight' => $weight, 'available' => $value !== null, 'evidence' => $this->evidence($name, $developer)];
        }

        $score = new ScoreSnapshot;
        $score->fill([
            'total' => $availableWeight > 0 ? round($weighted / $availableWeight, 2) : 0,
            'algorithm_version' => self::VERSION,
            'dimensions' => $dimensions,
            'calculated_at' => now(),
        ]);
        $score->developer()->associate($developer);
        $score->save();

        return $score;
    }

    private function cap(int $value, int $maximum): float
    {
        return round(min(100, ($value / $maximum) * 100), 2);
    }

    /** @return array<string, mixed> */
    private function evidence(string $dimension, Developer $developer): array
    {
        return match ($dimension) {
            'technical_impact' => ['stars' => $developer->total_stars],
            'contribution_quality' => ['repositories' => $developer->public_repositories, 'stars' => $developer->total_stars],
            'consistency' => ['last_activity_at' => $developer->last_activity_at?->toISOString()],
            'technical_depth' => ['languages' => $developer->languages],
            'collaboration' => ['followers' => $developer->followers],
            default => ['fields' => 'public profile'],
        };
    }
}
