<?php

namespace App\Http\Requests;

use App\Models\Developer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeveloperDiscoveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Developer::class);
    }

    public function rules(): array
    {
        return [
            'username' => ['nullable', 'string', 'max:39', 'regex:/^[a-zA-Z0-9-]+$/'],
            'language' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:80'],
            'min_repositories' => ['nullable', 'integer', 'between:0,1000'],
            'min_stars' => ['nullable', 'integer', 'between:0,100000000'],
            'sort' => ['nullable', Rule::in(['followers', 'repositories', 'joined'])],
            'page' => ['nullable', 'integer', 'between:1,100'],
        ];
    }
}
