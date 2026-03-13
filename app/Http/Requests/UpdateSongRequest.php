<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSongRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string',
            'file_path' => 'sometimes|file',

            'artist_id' => 'sometimes|nullable|array',
            'artist_id.*' => 'integer|exists:artists,id',
            'categories' => 'sometimes|nullable|array',
            'categories.*' => 'integer|exists:categories,id',

            'cover' => 'nullable|sometimes|file',
            'description' => 'nullable|sometimes',
            'duration' => 'nullable|sometimes|integer',
            'description' => 'nullable|sometimes',

        ];
    }
}
