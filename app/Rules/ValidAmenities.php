<?php

namespace App\Rules;

use App\Enums\Amenity;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidAmenities implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('The :attribute must be an array.');

            return;
        }

        $validAmenityTypes = array_map(fn ($case) => $case->value, Amenity::cases());

        foreach ($value as $type => $subtypes) {
            // Check if the amenity type is valid
            if (! in_array($type, $validAmenityTypes)) {
                $fail("The amenity type '{$type}' is invalid.");

                continue;
            }

            // Check if subtypes is an array
            if (! is_array($subtypes)) {
                $fail("The subtypes for '{$type}' must be an array.");

                continue;
            }

            // Get valid subtypes for this amenity type
            $amenityCase = Amenity::from($type);
            $validSubtypes = $amenityCase->getSubtypes();

            // Check each subtype
            foreach ($subtypes as $subtype) {
                if (! in_array($subtype, $validSubtypes)) {
                    $fail("The subtype '{$subtype}' is not valid for amenity type '{$type}'.");
                }
            }
        }
    }
}
