<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSongRequest extends FormRequest
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
            'title' => 'required|string',
            'file_path' => 'required|file',
            // 'file_path' => 'required|file|mimes:mp3|max:20480',

            'artist_id' => 'required|array',
            'artist_id.*' => 'integer|exists:artists,id',
            'categories' => 'required|array',
            'categories.*' => 'integer|exists:categories,id',

            'cover' => 'nullable|file',
            'description' => 'nullable',
            'duration' => 'nullable|integer',
            'description' => 'nullable',

        ];
    }
}
