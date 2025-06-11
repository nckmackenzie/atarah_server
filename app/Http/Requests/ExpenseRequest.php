<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
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
            'expense_date' => ['required', 'date','before_or_equal:tomorrow'],
            'payee' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'in:cash,cheque,mpesa,bank'],
            'payment_reference' => ['required', 'string'],
            'details' => ['required', 'array'],
            'details.*.gl_account_id' => ['required', 'exists:gl_accounts,id'],
            'details.*.project_id' => ['nullable', 'exists:projects,id'],
            'details.*.description' => ['nullable', 'string','max:255'],
            'details.*.amount' => ['required', 'numeric', 'gt:0'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'attachments_to_delete' => ['nullable', 'array'],
            'attachments_to_delete.*.id' => ['required', 'integer', 'exists:expense_attachments,id'],
        ];
    }
}
