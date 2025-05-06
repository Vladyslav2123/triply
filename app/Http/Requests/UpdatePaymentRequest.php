<?php

namespace App\Http\Requests;

use App\Enums\PaymentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UpdatePaymentRequest",
 *
 *     @OA\Property(property="status", type="string", enum={"pending", "processing", "completed", "failed", "refunded", "partially_refunded", "disputed"}, example="completed"),
 *     @OA\Property(property="refunded_amount", type="integer", example=10000, description="Amount refunded in cents"),
 *     @OA\Property(property="refunded_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="transaction_details", type="object")
 * )
 */
class UpdatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $payment = $this->route('payment');

        return $this->user()->can('update', $payment);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $payment = $this->route('payment');

        return [
            'status' => ['sometimes', 'required', Rule::in(PaymentStatus::values())],
            'refunded_amount' => [
                'sometimes',
                'required_if:status,'.PaymentStatus::REFUNDED->value.','.PaymentStatus::PARTIALLY_REFUNDED->value,
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($payment) {
                    if (in_array($this->status, [PaymentStatus::REFUNDED->value, PaymentStatus::PARTIALLY_REFUNDED->value])) {
                        if ($value > $payment->amount->getAmount()) {
                            $fail('The refunded amount cannot exceed the original payment amount.');
                        }

                        if ($this->status === PaymentStatus::REFUNDED->value && $value < $payment->amount->getAmount()) {
                            $fail('For a full refund, the refunded amount must equal the original payment amount.');
                        }

                        if ($this->status === PaymentStatus::PARTIALLY_REFUNDED->value && $value >= $payment->amount->getAmount()) {
                            $fail('For a partial refund, the refunded amount must be less than the original payment amount.');
                        }
                    }
                },
            ],
            'refunded_at' => [
                'sometimes',
                'required_if:status,'.PaymentStatus::REFUNDED->value.','.PaymentStatus::PARTIALLY_REFUNDED->value,
                'date',
            ],
            'transaction_details' => ['sometimes', 'array'],
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
            'status.required' => 'The payment status is required.',
            'status.in' => 'The selected payment status is invalid.',
            'refunded_amount.required_if' => 'The refunded amount is required when status is refunded or partially refunded.',
            'refunded_amount.integer' => 'The refunded amount must be an integer (in cents).',
            'refunded_amount.min' => 'The refunded amount must be at least 1 cent.',
            'refunded_at.required_if' => 'The refund date is required when status is refunded or partially refunded.',
            'refunded_at.date' => 'The refund date must be a valid date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If refunded_amount is provided as a float (dollars), convert to cents
        if ($this->has('refunded_amount') && is_float($this->refunded_amount)) {
            $this->merge([
                'refunded_amount' => (int) ($this->refunded_amount * 100),
            ]);
        }
    }
}
