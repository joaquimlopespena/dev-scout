<?php

namespace App\Http\Requests;

use App\Models\Developer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeveloperSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Developer::class);
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:80'], 'location' => ['nullable', 'string', 'max:80'], 'language' => ['nullable', 'string', 'max:50'], 'min_score' => ['nullable', 'numeric', 'between:0,100'], 'favorite' => ['nullable', 'boolean'], 'pipeline' => ['nullable', Rule::in(['sourced', 'screening', 'interview', 'offer', 'hired', 'rejected'])]];
    }
}
