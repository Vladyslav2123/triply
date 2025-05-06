<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::query()->inRandomOrder()->limit(2)->get();

        if ($users->count() < 2) {
            $sender = User::factory()->create();
            $recipient = User::factory()->create();
        } else {
            $sender = $users[0];
            $recipient = $users[1];
        }

        return [
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'content' => $this->faker->paragraph(rand(1, 5)),
            'sent_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'read_at' => null,
        ];
    }

    /**
     * Configure the factory to create a message from a specific sender.
     */
    public function fromSender(User $sender): static
    {
        return $this->state(function () use ($sender) {
            return [
                'sender_id' => $sender->id,
            ];
        });
    }

    /**
     * Configure the factory to create a message to a specific recipient.
     */
    public function toRecipient(User $recipient): static
    {
        return $this->state(function () use ($recipient) {
            return [
                'recipient_id' => $recipient->id,
            ];
        });
    }

    /**
     * Configure the factory to create a message between specific users.
     */
    public function between(User $sender, User $recipient): static
    {
        return $this->state(function () use ($sender, $recipient) {
            return [
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
            ];
        });
    }

    /**
     * Configure the factory to create a message that has been read.
     */
    public function read(): static
    {
        return $this->state(function (array $attributes) {
            $sentAt = $attributes['sent_at'] ?? now();
            $readAt = $this->faker->dateTimeBetween($sentAt, 'now');

            return [
                'read_at' => $readAt,
            ];
        });
    }

    /**
     * Configure the factory to create a message with specific content.
     */
    public function withContent(string $content): static
    {
        return $this->state(function () use ($content) {
            return [
                'content' => $content,
            ];
        });
    }

    /**
     * Configure the factory to create a message sent at a specific time.
     */
    public function sentAt(DateTime $sentAt): static
    {
        return $this->state(function () use ($sentAt) {
            return [
                'sent_at' => $sentAt,
            ];
        });
    }
}
