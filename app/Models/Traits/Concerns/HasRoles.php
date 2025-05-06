<?php

namespace App\Models\Traits\Concerns;

use App\Enums\UserRole;

trait HasRoles
{
    /**
     * Check if the user has any of the given roles.
     *
     * @param  array<UserRole>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::ADMIN);
    }

    /**
     * Check if the user has the given role.
     */
    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if the user is a host.
     */
    public function isHost(): bool
    {
        return $this->hasRole(UserRole::HOST);
    }

    /**
     * Check if the user is a regular user.
     */
    public function isUser(): bool
    {
        return $this->hasRole(UserRole::USER);
    }

    /**
     * Check if the user is a guest.
     */
    public function isGuest(): bool
    {
        return $this->hasRole(UserRole::GUEST);
    }

    /**
     * Set the user's role.
     */
    public function setRole(UserRole $role): void
    {
        $this->role = $role;
    }
}
