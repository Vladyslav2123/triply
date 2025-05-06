<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Cknow\Money\Money;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CreateListing
{
    /**
     * Create a new listing with automatic slug generation if not provided.
     *
     * @param  array  $data  The listing data
     * @return Listing The created listing
     *
     * @throws Exception If the listing creation fails
     */
    public function execute(array $data): Listing
    {
        try {
            // Generate slug if not provided
            if (empty($data['slug']) && ! empty($data['title'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title']);
            }

            // Handle price_per_night if it's an array
            if (isset($data['price_per_night']) && is_array($data['price_per_night'])) {
                $data['price_per_night'] = new Money(
                    $data['price_per_night']['amount'],
                    $data['price_per_night']['currency'] ?? 'UAH'
                );
            }

            // Use a transaction to ensure data integrity
            return DB::transaction(function () use ($data) {
                return Listing::create($data);
            });
        } catch (Throwable $e) {
            throw new RuntimeException('Failed to create listing: '.$e->getMessage(), 500, $e);
        }
    }

    /**
     * Generate a unique slug for a listing.
     *
     * @param  string  $title  The listing title
     * @return string The generated unique slug
     */
    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug.'-'.Str::lower(Str::random(6));

        // Check if the slug already exists and generate a new one if needed
        $count = 1;
        while (Listing::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.Str::lower(Str::random(6));

            // Safety check to prevent infinite loops
            if ($count++ > 10) {
                $slug = $baseSlug.uniqid('-', true);
                break;
            }
        }

        return $slug;
    }
}
