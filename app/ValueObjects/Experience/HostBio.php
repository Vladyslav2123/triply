<?php

namespace App\ValueObjects\Experience;

use App\ValueObjects\AbstractValueObject;

final class HostBio extends AbstractValueObject
{
    public function __construct(
        public readonly bool $is_team_based,
        public readonly string $about
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            is_team_based: $data['is_team_based'] ?? false,
            about: $data['about'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'is_team_based' => $this->is_team_based,
            'about' => $this->about,
        ];
    }
}
