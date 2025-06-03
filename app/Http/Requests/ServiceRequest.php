<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
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
            'name' => ['required','string','max:255',Rule::unique('services')->ignore($this->service?->id)->withoutTrashed()],
            'description' => ['nullable', 'string', 'max:1000'],
            'rate' => ['required', 'numeric', 'min:0'],
            'gl_account_id' => ['required', 'exists:gl_accounts,id'],
            'active' => ['required', 'boolean'],
        ];
    }
}
