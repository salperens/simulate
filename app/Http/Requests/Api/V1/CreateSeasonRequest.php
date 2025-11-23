<?php

namespace App\Http\Requests\Api\V1;

use App\Data\Season\CreateSeasonData;
use Illuminate\Foundation\Http\FormRequest;

class CreateSeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year'       => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],
            'team_ids'   => [
                'required',
                'array',
                'min:2',
            ],
            'team_ids.*' => [
                'required',
                'integer',
                'exists:teams,id',
            ],
            'name'       => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'year.required'     => 'Year is required.',
            'year.integer'      => 'Year must be an integer.',
            'year.min'          => 'Year must be at least 2000.',
            'year.max'          => 'Year cannot exceed 2100.',
            'team_ids.required' => 'At least 2 teams must be selected.',
            'team_ids.array'    => 'Team IDs must be an array.',
            'team_ids.min'      => 'At least 2 teams must be selected.',
            'team_ids.*.exists' => 'One or more selected teams do not exist.',
        ];
    }

    public function toData(): CreateSeasonData
    {
        return CreateSeasonData::from($this->validated());
    }
}
