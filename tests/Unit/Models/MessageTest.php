<?php

namespace Tests\Unit\Models;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_uses_ulids_for_ids(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $message = Message::factory()->create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
        ]);

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{26}$/', $message->id);
    }

    #[Test]
    public function it_has_sender_relationship(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $message = Message::factory()->create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
        ]);

        $this->assertInstanceOf(User::class, $message->sender);
        $this->assertEquals($sender->id, $message->sender->id);
    }

    #[Test]
    public function it_has_recipient_relationship(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $message = Message::factory()->create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
        ]);

        $this->assertInstanceOf(User::class, $message->recipient);
        $this->assertEquals($recipient->id, $message->recipient->id);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $message = new Message;
        $casts = $message->getCasts();

        $this->assertEquals('datetime:Y-m-d H:i', $casts['sent_at']);
    }

    #[Test]
    public function it_hides_timestamps(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $message = Message::factory()->create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
        ]);

        $array = $message->toArray();

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }

    #[Test]
    public function it_can_store_message_content(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $content = 'This is a test message content';

        $message = Message::factory()->create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'content' => $content,
        ]);

        $this->assertEquals($content, $message->content);
    }

    #[Test]
    public function it_can_filter_by_sender(): void
    {
        $sender1 = User::factory()->create();
        $sender2 = User::factory()->create();
        $recipient = User::factory()->create();

        $message1 = Message::factory()->create([
            'sender_id' => $sender1->id,
            'recipient_id' => $recipient->id,
        ]);

        $message2 = Message::factory()->create([
            'sender_id' => $sender2->id,
            'recipient_id' => $recipient->id,
        ]);

        $filtered = Message::query()
            ->where('sender_id', $sender1->id)
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals($message1->id, $filtered->first()->id);
    }

    #[Test]
    public function it_can_filter_by_recipient(): void
    {
        $sender = User::factory()->create();
        $recipient1 = User::factory()->create();
        $recipient2 = User::factory()->create();

        $message1 = Message::factory()->create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient1->id,
        ]);

        $message2 = Message::factory()->create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient2->id,
        ]);

        $filtered = Message::query()
            ->where('recipient_id', $recipient1->id)
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals($message1->id, $filtered->first()->id);
    }

    #[Test]
    public function it_can_filter_by_sent_at(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $yesterday = now()->subDay();
        $today = now();

        $message1 = Message::factory()->create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'sent_at' => $yesterday,
        ]);

        $message2 = Message::factory()->create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'sent_at' => $today,
        ]);

        $filtered = Message::query()
            ->whereDate('sent_at', $yesterday->format('Y-m-d'))
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals($message1->id, $filtered->first()->id);
    }

    #[Test]
    public function it_can_get_conversation_between_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $message1 = Message::factory()->create([
            'sender_id' => $user1->id,
            'recipient_id' => $user2->id,
        ]);

        $message2 = Message::factory()->create([
            'sender_id' => $user2->id,
            'recipient_id' => $user1->id,
        ]);

        $user3 = User::factory()->create();
        $message3 = Message::factory()->create([
            'sender_id' => $user1->id,
            'recipient_id' => $user3->id,
        ]);

        $conversation = Message::query()
            ->where(function ($query) use ($user1, $user2) {
                $query->where('sender_id', $user1->id)
                    ->where('recipient_id', $user2->id);
            })
            ->orWhere(function ($query) use ($user1, $user2) {
                $query->where('sender_id', $user2->id)
                    ->where('recipient_id', $user1->id);
            })
            ->orderBy('sent_at')
            ->get();

        $this->assertCount(2, $conversation);
        $this->assertTrue($conversation->contains('id', $message1->id));
        $this->assertTrue($conversation->contains('id', $message2->id));
        $this->assertFalse($conversation->contains('id', $message3->id));
    }
}
