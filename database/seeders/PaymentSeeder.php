<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\ReservationStatus;
use App\Models\Experience;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Reservation;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Payment Seeder');

        // Create a summary table for reporting
        $summary = [
            'total_reservations' => 0,
            'reservations_with_payments' => 0,
            'reservations_without_payments' => 0,
            'payments_created' => 0,
            'payments_failed' => 0,
            'listing_reservations' => 0,
            'experience_reservations' => 0,
        ];

        // Get statistics before seeding
        $summary['total_reservations'] = Reservation::count();
        $summary['reservations_with_payments'] = Reservation::has('payment')->count();
        $summary['reservations_without_payments'] = $summary['total_reservations'] - $summary['reservations_with_payments'];

        // Find all confirmed and completed reservations that don't have payments yet
        $reservations = Reservation::whereIn('status', [
            ReservationStatus::CONFIRMED,
            ReservationStatus::COMPLETED,
        ])
            ->whereDoesntHave('payment')
            ->get();

        $this->command->info("Found {$reservations->count()} reservations without payments");

        // Count by type
        foreach ($reservations as $reservation) {
            if ($reservation->reservationable_type === Listing::class) {
                $summary['listing_reservations']++;
            } elseif ($reservation->reservationable_type === Experience::class) {
                $summary['experience_reservations']++;
            }
        }

        // Use a transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // Create payments for each reservation
            foreach ($reservations as $reservation) {
                try {
                    // Determine if we should create a failed payment (10% chance)
                    $shouldFail = rand(1, 10) === 1;

                    if ($shouldFail) {
                        // Create a failed payment
                        Payment::factory()
                            ->forReservation($reservation)
                            ->withPaymentMethod($this->getRandomPaymentMethod())
                            ->failed()
                            ->create();

                        $this->command->warn("Created failed payment for reservation: {$reservation->id}");
                    } else {
                        // Create a completed payment
                        Payment::factory()
                            ->forReservation($reservation)
                            ->withPaymentMethod($this->getRandomPaymentMethod())
                            ->completed()
                            ->create();

                        $this->command->info("Created completed payment for reservation: {$reservation->id}");
                    }

                    $summary['payments_created']++;
                } catch (Exception $e) {
                    $summary['payments_failed']++;
                    Log::error("Failed to create payment for reservation: {$reservation->id}", [
                        'error' => $e->getMessage(),
                    ]);
                    $this->command->error("Failed to create payment for reservation: {$reservation->id} - {$e->getMessage()}");
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to seed payments', [
                'error' => $e->getMessage(),
            ]);
            $this->command->error("Failed to seed payments: {$e->getMessage()}");

            return;
        }

        // Display summary
        $this->command->info('\nPayment Seeding Summary:');
        $this->command->info('------------------------');
        $this->command->info("Total Reservations: {$summary['total_reservations']}");
        $this->command->info("Reservations With Payments (before): {$summary['reservations_with_payments']}");
        $this->command->info("Reservations Without Payments (before): {$summary['reservations_without_payments']}");
        $this->command->info("Payments Created: {$summary['payments_created']}");
        $this->command->info("Payments Failed: {$summary['payments_failed']}");
        $this->command->info("Listing Reservations Processed: {$summary['listing_reservations']}");
        $this->command->info("Experience Reservations Processed: {$summary['experience_reservations']}");
        $this->command->info('------------------------');

        // Get statistics after seeding
        $reservationsWithPaymentsAfter = Reservation::has('payment')->count();
        $reservationsWithoutPaymentsAfter = Reservation::count() - $reservationsWithPaymentsAfter;

        $this->command->info("Reservations With Payments (after): {$reservationsWithPaymentsAfter}");
        $this->command->info("Reservations Without Payments (after): {$reservationsWithoutPaymentsAfter}");
        $this->command->info('------------------------');

        $this->command->info('Payment seeding completed');
    }

    /**
     * Get a random payment method.
     */
    private function getRandomPaymentMethod(): PaymentMethod
    {
        $methods = PaymentMethod::cases();

        return $methods[array_rand($methods)];
    }
}
