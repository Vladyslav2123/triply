<?php

namespace Tests\Feature\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

class MessageControllerTest extends ApiControllerTestCase
{
    use WithFaker;

    private User $sender;

    private User $recipient;

    private Message $message;

    protected function setUp(): void
    {
        parent::setUp();

        // Create sender and recipient users
        $this->sender = User::factory()->create();
        $this->recipient = User::factory()->create();

        // Create a message
        $this->message = Message::factory()->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
            'content' => 'Test message content',
            'sent_at' => now(),
        ]);
    }

    /**
     * Test retrieving a list of messages
     */
    public function test_index_returns_user_messages(): void
    {
        // Create additional messages
        Message::factory()->count(3)->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
        ]);

        $response = $this->actingAs($this->sender)
            ->getJson('/api/v1/messages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'sender_id',
                        'recipient_id',
                        'sent_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    /**
     * Test retrieving a specific message
     */
    public function test_show_returns_message_details(): void
    {
        $response = $this->actingAs($this->sender)
            ->getJson("/api/v1/messages/{$this->message->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'content',
                'sender_id',
                'recipient_id',
                'sent_at',
            ])
            ->assertJsonPath('id', $this->message->id);
    }

    /**
     * Test authorization when viewing a message
     */
    public function test_show_requires_authorization(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->getJson("/api/v1/messages/{$this->message->id}");

        $response->assertStatus(403);
    }

    /**
     * Test sending a new message
     */
    public function test_store_creates_new_message(): void
    {
        $messageData = [
            'recipient_id' => $this->recipient->id,
            'content' => 'This is a new test message',
        ];

        $response = $this->actingAs($this->sender)
            ->postJson('/api/v1/messages', $messageData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'content',
                'sender_id',
                'recipient_id',
                'sent_at',
            ]);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
            'content' => 'This is a new test message',
        ]);
    }

    /**
     * Test validation when sending a message
     */
    public function test_store_validates_message_data(): void
    {
        $response = $this->actingAs($this->sender)
            ->postJson('/api/v1/messages', [
                // Missing required fields
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recipient_id', 'content']);
    }

    /**
     * Test validation of message content length
     */
    public function test_store_validates_content_length(): void
    {
        $messageData = [
            'recipient_id' => $this->recipient->id,
            'content' => str_repeat('a', 5001), // Assuming max length is 5000
        ];

        $response = $this->actingAs($this->sender)
            ->postJson('/api/v1/messages', $messageData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    /**
     * Test updating a message
     */
    public function test_update_modifies_message(): void
    {
        $updateData = [
            'content' => 'Updated message content',
        ];

        $response = $this->actingAs($this->sender)
            ->putJson("/api/v1/messages/{$this->message->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('content', 'Updated message content');

        $this->assertDatabaseHas('messages', [
            'id' => $this->message->id,
            'content' => 'Updated message content',
        ]);
    }

    /**
     * Test authorization when updating a message
     */
    public function test_update_requires_authorization(): void
    {
        $updateData = [
            'content' => 'Unauthorized update attempt',
        ];

        // Recipient should not be able to update sender's message
        $response = $this->actingAs($this->recipient)
            ->putJson("/api/v1/messages/{$this->message->id}", $updateData);

        $response->assertStatus(403);
    }

    /**
     * Test deleting a message
     */
    public function test_destroy_deletes_message(): void
    {
        $response = $this->actingAs($this->sender)
            ->deleteJson("/api/v1/messages/{$this->message->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('messages', ['id' => $this->message->id]);
    }

    /**
     * Test authorization when deleting a message
     */
    public function test_destroy_requires_authorization(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/v1/messages/{$this->message->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('messages', ['id' => $this->message->id]);
    }

    /**
     * Test retrieving conversation between users
     */
    public function test_get_conversation_between_users(): void
    {
        // Create additional messages between the same users
        Message::factory()->count(3)->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
        ]);

        Message::factory()->count(2)->create([
            'sender_id' => $this->recipient->id,
            'recipient_id' => $this->sender->id,
        ]);

        $response = $this->actingAs($this->sender)
            ->getJson("/api/v1/messages/conversation/{$this->recipient->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'sender_id',
                        'recipient_id',
                        'sent_at',
                    ],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(6, 'data'); // 1 original + 5 additional messages
    }

    /**
     * Test admin can access any message
     */
    public function test_admin_can_access_any_message(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/messages/{$this->message->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $this->message->id);
    }

    /**
     * Test marking messages as read
     */
    public function test_mark_messages_as_read(): void
    {
        // Create unread messages
        $unreadMessages = Message::factory()->count(3)->create([
            'sender_id' => $this->sender->id,
            'recipient_id' => $this->recipient->id,
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->recipient)
            ->postJson('/api/v1/messages/mark-read', [
                'message_ids' => $unreadMessages->pluck('id')->toArray(),
            ]);

        $response->assertStatus(200);

        foreach ($unreadMessages as $message) {
            $this->assertDatabaseHas('messages', [
                'id' => $message->id,
                'read_at' => now()->toDateTimeString(),
            ]);
        }
    }
}
