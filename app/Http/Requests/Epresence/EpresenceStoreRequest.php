<?php

namespace App\Http\Requests\Epresence;

use Illuminate\Foundation\Http\FormRequest;

class EpresenceStoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:IN,OUT',
            'waktu' => 'required|date_format:Y-m-d H:i:s'
        ];
    }
}
