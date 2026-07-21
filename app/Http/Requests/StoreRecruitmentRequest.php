<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecruitmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateRecruitment', $this->route('developer'));
    }

    public function rules(): array
    {
        return ['favorite' => ['sometimes', 'boolean'], 'note' => ['nullable', 'string', 'max:4000'], 'status' => ['nullable', Rule::in(['sourced', 'screening', 'interview', 'offer', 'hired', 'rejected'])], 'tags' => ['sometimes', 'array', 'max:10'], 'tags.*' => ['string', 'max:40', 'distinct']];
    }
}
