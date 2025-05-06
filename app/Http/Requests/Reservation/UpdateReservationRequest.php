<?php

namespace App\Http\Requests\Reservation;

use App\Enums\ReservationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UpdateReservationRequest",
 *
 *     @OA\Property(property="reservationable_type", type="string", enum={"listing", "experience"}, example="listing"),
 *     @OA\Property(property="reservationable_id", type="string", format="ulid", example="01HGW4D3HXN4NCOPY4QJXJ8CVT"),
 *     @OA\Property(property="check_in", type="string", format="date", example="2024-06-15"),
 *     @OA\Property(property="check_out", type="string", format="date", example="2024-06-20"),
 *     @OA\Property(property="total_price", type="integer", example=1000000),
 *     @OA\Property(property="status", type="string", enum={"pending", "confirmed", "cancelled", "completed"}, example="confirmed")
 * )
 */
class UpdateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('reservation'));
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
            'reservationable_type' => 'sometimes|string|in:listing,experience',
            'reservationable_id' => 'sometimes|ulid',
            'check_in' => [
                'sometimes',
                'date',
                // Only enforce future dates for pending reservations
                $reservation && $reservation->status === ReservationStatus::PENDING
                    ? 'after_or_equal:today'
                    : '',
            ],
            'check_out' => 'sometimes|date|after:check_in',
            'total_price' => 'sometimes|integer|min:0',
            'status' => ['sometimes', Rule::in(ReservationStatus::values())],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Map reservationable_type values to model namespaces
        if ($this->has('reservationable_type')) {
            $typeMap = [
                'listing' => 'App\\Models\\Listing',
                'experience' => 'App\\Models\\Experience',
            ];

            $type = $this->input('reservationable_type');
            if (isset($typeMap[$type])) {
                $this->merge([
                    'reservationable_type' => $typeMap[$type],
                ]);
            }
        }
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reservationable_type.in' => 'Тип об\'єкту бронювання має бути одним із: listing, experience.',
            'reservationable_id.ulid' => 'ID об\'єкту бронювання має бути у форматі ULID.',
            'check_in.date' => 'Дата заїзду має бути коректною датою.',
            'check_in.after_or_equal' => 'Дата заїзду не може бути в минулому.',
            'check_out.date' => 'Дата виїзду має бути коректною датою.',
            'check_out.after' => 'Дата виїзду має бути після дати заїзду.',
            'total_price.integer' => 'Ціна має бути числом.',
            'total_price.min' => 'Ціна не може бути від\'ємною.',
            'status.in' => 'Статус має бути одним із: '.implode(', ', ReservationStatus::values()).'.',
        ];
    }
}
