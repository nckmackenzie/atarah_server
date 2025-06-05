<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
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
            'invoice_no' => ['required', 'string', 'max:255',Rule::unique('invoice_headers')->ignore($this->route('invoice'))->withoutTrashed()],
            'invoice_date' => ['required', 'date','before_or_equal:tomorrow'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'terms' => ['required','string','in:0,30,60'],
            'vat_type' => ['required', 'string', 'in:inclusive,exclusive,no_vat'],
            'vat' => ['nullable','required_if:vat_type,inclusive,exclusive','numeric','min:0'],
            'client_id' => ['required', 'exists:clients,id'],
            'items' => ['required', 'array'],
            'items.*.service_id' => ['required', 'string', 'exists:services,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
