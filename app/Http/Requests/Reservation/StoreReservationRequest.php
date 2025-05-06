<?php

namespace App\Http\Requests\Reservation;

use App\Models\Reservation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreReservationRequest",
 *     required={"guest_id", "reservationable_type", "reservationable_id", "check_in", "check_out", "total_price"},
 *
 *     @OA\Property(property="guest_id", type="string", format="ulid", example="01HGW4D3HXN4NCOPY4QJXJ8CVT"),
 *     @OA\Property(property="reservationable_type", type="string", enum={"listing", "experience"}, example="listing"),
 *     @OA\Property(property="reservationable_id", type="string", format="ulid", example="01HGW4D3HXN4NCOPY4QJXJ8CVT"),
 *     @OA\Property(property="check_in", type="string", format="date", example="2024-06-15"),
 *     @OA\Property(property="check_out", type="string", format="date", example="2024-06-20"),
 *     @OA\Property(property="total_price", type="integer", example=1000000)
 * )
 */
class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Reservation::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'guest_id' => 'required|exists:users,id',
            'reservationable_type' => 'required|string|in:listing,experience',
            'reservationable_id' => 'required|ulid',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'total_price' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'guest_id.required' => 'Гість є обов\'язковим полем.',
            'guest_id.exists' => 'Обраний гість не існує.',
            'reservationable_type.required' => 'Тип об\'єкту бронювання є обов\'язковим полем.',
            'reservationable_type.in' => 'Тип об\'єкту бронювання має бути одним із: listing, experience.',
            'reservationable_id.required' => 'ID об\'єкту бронювання є обов\'язковим полем.',
            'reservationable_id.ulid' => 'ID об\'єкту бронювання має бути у форматі ULID.',
            'check_in.required' => 'Дата заїзду є обов\'язковою.',
            'check_in.date' => 'Дата заїзду має бути коректною датою.',
            'check_in.after_or_equal' => 'Дата заїзду не може бути в минулому.',
            'check_out.required' => 'Дата виїзду є обов\'язковою.',
            'check_out.date' => 'Дата виїзду має бути коректною датою.',
            'check_out.after' => 'Дата виїзду має бути після дати заїзду.',
            'total_price.required' => 'Ціна є обов\'язковою.',
            'total_price.integer' => 'Ціна має бути числом.',
            'total_price.min' => 'Ціна не може бути від\'ємною.',
        ];
    }

    /**
     * Get the validated data with default values.
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();

        // Map reservationable_type values to model namespaces
        if (isset($validated['reservationable_type'])) {
            $typeMap = [
                'listing' => 'App\\Models\\Listing',
                'experience' => 'App\\Models\\Experience',
            ];

            $type = $validated['reservationable_type'];
            if (isset($typeMap[$type])) {
                $validated['reservationable_type'] = $typeMap[$type];
            }
        }

        // Set default status if not provided
        if (! isset($validated['status'])) {
            $validated['status'] = ReservationStatus::PENDING;
        }

        return $validated;
    }
}
