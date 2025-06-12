<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JournalRequest extends FormRequest
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
            'transaction_date' => ['required','date','before_or_equal:tomorrow'],
            'details' => ['required','array'],
            'details.*.description' => ['nullable','string'],
            'details.*.debit' => ['nullable','numeric','min:0'],
            'details.*.credit' => ['nullable','numeric','min:0'],
            'details.*.gl_account_id' => ['required','exists:gl_accounts,id'],
        ];
    }
}
