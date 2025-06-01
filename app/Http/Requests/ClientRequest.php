<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255',Rule::unique('clients')->ignore($this->client?->id)->withoutTrashed()],
            'email' => ['required', 'email', 'max:255', Rule::unique('clients')->withoutTrashed()->ignore($this->client?->id)],
            'tax_pin' => ['nullable', 'string', 'max:50', Rule::unique('clients')->withoutTrashed()->ignore($this->client?->id)],
            'contact' => ['required','string','max:10'],
            'address' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ];
    }
}
