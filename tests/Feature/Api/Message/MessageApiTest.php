<?php

namespace Tests\Feature\Api\Message;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    private User $sender;

    private User $recipient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sender = User::factory()->create();
        $this->recipient = User::factory()->create();
    }

    public function test_user_can_send_message(): void
    {
        $messageData = [
            'content' => 'Hello, this is a test message',
        ];

        $response = $this->actingAs($this->sender)
            ->postJson("/api/v1/users/{$this->recipient->id}/messages", $messageData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'content',
                'sent_at',
                'sender',
                'recipient',
            ]);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
            'content' => 'Hello, this is a test message',
        ]);
    }

    public function test_unauthenticated_user_cannot_send_message(): void
    {
        $messageData = [
            'content' => 'Hello, this is a test message',
        ];

        $response = $this->postJson("/api/v1/users/{$this->recipient->id}/messages", $messageData);

        $response->assertStatus(401);
    }

    public function test_user_can_get_conversation_with_another_user(): void
    {
        // Create messages from sender to recipient
        Message::factory()->count(3)->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
        ]);

        // Create messages from recipient to sender
        Message::factory()->count(2)->create([
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
        ]);

        // Create messages to/from other users (should not be included)
        $otherUser = User::factory()->create();
        Message::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->sender)
            ->getJson("/api/v1/conversations/{$this->recipient->id}");

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'sent_at',
                        'sender',
                        'recipient',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_user_can_get_all_conversations(): void
    {
        // Create conversations with multiple users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Conversation with user1
        Message::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $user1->id,
            'sent_at' => now()->subHours(3),
        ]);

        // Conversation with user2
        Message::factory()->create([
            'sender_id' => $user2->id,
            'recipient_id' => $this->sender->id,
            'sent_at' => now()->subHours(2),
        ]);

        // Conversation with user3
        Message::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $user3->id,
            'sent_at' => now()->subHours(1),
        ]);

        $response = $this->actingAs($this->sender)
            ->getJson('/api/v1/conversations');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'user' => [
                            'id',
                            'name',
                            'photo',
                        ],
                        'last_message' => [
                            'id',
                            'content',
                            'sent_at',
                        ],
                        'unread_count',
                    ],
                ],
            ]);
    }

    public function test_user_can_mark_conversation_as_read(): void
    {
        // Create unread messages
        Message::factory()->count(3)->create([
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->sender)
            ->postJson("/api/v1/conversations/{$this->recipient->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'All messages marked as read',
            ]);

        // Check that all messages are marked as read
        $this->assertDatabaseCount('messages', 3);
        $this->assertDatabaseMissing('messages', [
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
            'is_read' => false,
        ]);
    }

    public function test_user_can_delete_message(): void
    {
        $message = Message::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
        ]);

        $response = $this->actingAs($this->sender)
            ->deleteJson("/api/v1/messages/{$message->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('messages', [
            'id' => $message->id,
        ]);
    }

    public function test_user_cannot_delete_others_message(): void
    {
        $message = Message::factory()->create([
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
        ]);

        $response = $this->actingAs($this->sender)
            ->deleteJson("/api/v1/messages/{$message->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_get_unread_messages_count(): void
    {
        // Create unread messages from different users
        Message::factory()->count(3)->create([
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
            'is_read' => false,
        ]);

        $otherUser = User::factory()->create();
        Message::factory()->count(2)->create([
            'sender_id' => $otherUser->id,
            'recipient_id' => $this->sender->id,
            'is_read' => false,
        ]);

        // Create read messages (should not be counted)
        Message::factory()->create([
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
            'is_read' => true,
        ]);

        $response = $this->actingAs($this->sender)
            ->getJson('/api/v1/messages/unread-count');

        $response->assertStatus(200)
            ->assertJson([
                'total_unread' => 5,
                'by_user' => [
                    [
                        'user_id' => $this->recipient->id,
                        'count' => 3,
                    ],
                    [
                        'user_id' => $otherUser->id,
                        'count' => 2,
                    ],
                ],
            ]);
    }

    public function test_message_content_validation(): void
    {
        // Empty content
        $response = $this->actingAs($this->sender)
            ->postJson("/api/v1/users/{$this->recipient->id}/messages", [
                'content' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);

        // Content too long
        $response = $this->actingAs($this->sender)
            ->postJson("/api/v1/users/{$this->recipient->id}/messages", [
                'content' => str_repeat('a', 5001), // Assuming max length is 5000
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }
}
