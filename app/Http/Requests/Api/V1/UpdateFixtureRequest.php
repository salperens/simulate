<?php

namespace App\Http\Requests\Api\V1;

use App\Data\Fixture\UpdateFixtureData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateFixtureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'home_score' => [
                'required',
                'integer',
                'min:0',
            ],
            'away_score' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'home_score.required' => 'Home score is required.',
            'home_score.integer'  => 'Home score must be an integer.',
            'home_score.min'      => 'Home score cannot be negative.',
            'away_score.required' => 'Away score is required.',
            'away_score.integer'  => 'Away score must be an integer.',
            'away_score.min'      => 'Away score cannot be negative.',
        ];
    }

    /**
     * Convert validated request data to UpdateFixtureData.
     */
    public function toData(): UpdateFixtureData
    {
        return UpdateFixtureData::from($this->validated());
    }
}
