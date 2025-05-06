<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="StorePaymentRequest",
 *     required={"amount", "payment_method"},
 *
 *     @OA\Property(property="amount", type="integer", example=50000, description="Amount in cents"),
 *     @OA\Property(property="payment_method", type="string", enum={"credit_card", "paypal", "bank_transfer"}, example="credit_card"),
 *     @OA\Property(property="transaction_id", type="string", example="txn_123456789"),
 *     @OA\Property(property="transaction_details", type="object")
 * )
 */
class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $reservation = $this->route('reservation');

        return $this->user()->can('createForReservation', [$reservation]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $reservation = $this->route('reservation');

        return [
            'amount' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($reservation) {
                    $remainingBalance = $reservation->getRemainingBalance()->getAmount();
                    if ($value > $remainingBalance) {
                        $fail("The payment amount cannot exceed the remaining balance of {$remainingBalance} cents.");
                    }
                },
            ],
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'transaction_id' => ['nullable', 'string', 'unique:payments,transaction_id'],
            'transaction_details' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'The payment amount is required.',
            'amount.integer' => 'The payment amount must be an integer (in cents).',
            'amount.min' => 'The payment amount must be at least 1 cent.',
            'payment_method.required' => 'The payment method is required.',
            'payment_method.in' => 'The selected payment method is invalid.',
            'transaction_id.unique' => 'This transaction ID has already been used.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If amount is provided as a float (dollars), convert to cents
        if ($this->has('amount') && is_float($this->amount)) {
            $this->merge([
                'amount' => (int) ($this->amount * 100),
            ]);
        }
    }
}
