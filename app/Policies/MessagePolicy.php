<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MessagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return Response::allow('Користувачі можуть переглядати власні повідомлення.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Message $message): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return $user->id === $message->sender_id || $user->id === $message->recipient_id
            ? Response::allow()
            : Response::deny('Ви можете переглядати тільки власні повідомлення.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return Response::allow('Авторизовані користувачі можуть надсилати повідомлення.');
    }

    /**
     * Determine whether the user can send a message to a specific user.
     */
    public function sendTo(User $sender, User $recipient): Response
    {
        if ($sender->id === $recipient->id) {
            return Response::deny('Ви не можете надсилати повідомлення самому собі.');
        }

        // Check if the recipient has blocked the sender
        if (method_exists($recipient, 'hasBlocked') && $recipient->hasBlocked($sender)) {
            return Response::deny('Ви не можете надсилати повідомлення цьому користувачу.');
        }

        // Check if the sender has been blocked by the recipient
        if (method_exists($sender, 'isBlockedBy') && $sender->isBlockedBy($recipient)) {
            return Response::deny('Ви не можете надсилати повідомлення цьому користувачу.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Message $message): Response
    {
        return Response::deny('Повідомлення не можна редагувати після надсилання.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Message $message): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        if ($user->id !== $message->sender_id && $user->id !== $message->recipient_id) {
            return Response::deny('Ви можете видаляти тільки власні повідомлення.');
        }

        // Check if message is too old to delete (e.g., 24 hours)
        if ($message->created_at->lt(now()->subHours(24))) {
            return Response::deny('Повідомлення можна видалити тільки протягом 24 годин після надсилання.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Message $message): Response
    {
        if ($user->role !== UserRole::ADMIN) {
            return Response::deny('Тільки адміністратор може відновлювати видалені повідомлення.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Message $message): Response
    {
        if ($user->role !== UserRole::ADMIN) {
            return Response::deny('Тільки адміністратор може остаточно видалити повідомлення.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can report the message.
     */
    public function report(User $user, Message $message): Response
    {
        if ($user->id === $message->sender_id) {
            return Response::deny('Ви не можете поскаржитись на власне повідомлення.');
        }

        if ($user->id !== $message->recipient_id) {
            return Response::deny('Ви можете скаржитись тільки на отримані повідомлення.');
        }

        // Check if the message is too old to report (e.g., 30 days)
        if ($message->created_at->lt(now()->subDays(30))) {
            return Response::deny('На повідомлення можна поскаржитись тільки протягом 30 днів після отримання.');
        }

        // Check if the user has already reported this message
        if ($message->reports()->where('user_id', $user->id)->exists()) {
            return Response::deny('Ви вже скаржились на це повідомлення.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can mark the message as read.
     */
    public function markAsRead(User $user, Message $message): Response
    {
        if ($user->id !== $message->recipient_id) {
            return Response::deny('Ви можете позначати як прочитані тільки отримані повідомлення.');
        }

        return Response::allow();
    }
}
