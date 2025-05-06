<?php

namespace App\ValueObjects\Experience;

use App\ValueObjects\AbstractValueObject;

final class HostVerification extends AbstractValueObject
{
    public function __construct(
        public readonly bool $is_verified = false,
        public readonly bool $is_phone_verified = false,
        public readonly bool $is_identity_verified = false,
        public readonly ?string $verification_status = null,
        public readonly ?string $verification_notes = null,
        public readonly ?string $verification_documents = null,
        public readonly ?\DateTime $verified_at = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            is_verified: $data['is_verified'] ?? false,
            is_phone_verified: $data['is_phone_verified'] ?? false,
            is_identity_verified: $data['is_identity_verified'] ?? false,
            verification_status: $data['verification_status'] ?? null,
            verification_notes: $data['verification_notes'] ?? null,
            verification_documents: $data['verification_documents'] ?? null,
            verified_at: isset($data['verified_at']) ? new \DateTime($data['verified_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'is_verified' => $this->is_verified,
            'is_phone_verified' => $this->is_phone_verified,
            'is_identity_verified' => $this->is_identity_verified,
            'verification_status' => $this->verification_status,
            'verification_notes' => $this->verification_notes,
            'verification_documents' => $this->verification_documents,
            'verified_at' => $this->verified_at?->format('Y-m-d H:i:s'),
        ];
    }
}
