<?php

namespace App\ValueObjects\Experience;

use App\ValueObjects\AbstractValueObject;

final class HostLicenses extends AbstractValueObject
{
    public function __construct(
        public readonly bool $has_local_law_knowledge = false,
        public readonly bool $has_guide_license = false,
        public readonly bool $accepts_host_standards = false,
        public readonly bool $accepts_compensation_rules = false,
        public readonly ?string $license_number = null,
        public readonly ?string $license_type = null,
        public readonly ?string $license_issuer = null,
        public readonly ?\DateTime $license_expiry = null,
        public readonly ?array $additional_documents = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            has_local_law_knowledge: $data['has_local_law_knowledge'] ?? false,
            has_guide_license: $data['has_guide_license'] ?? false,
            accepts_host_standards: $data['accepts_host_standards'] ?? false,
            accepts_compensation_rules: $data['accepts_compensation_rules'] ?? false,
            license_number: $data['license_number'] ?? null,
            license_type: $data['license_type'] ?? null,
            license_issuer: $data['license_issuer'] ?? null,
            license_expiry: isset($data['license_expiry']) ? new \DateTime($data['license_expiry']) : null,
            additional_documents: $data['additional_documents'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'has_local_law_knowledge' => $this->has_local_law_knowledge,
            'has_guide_license' => $this->has_guide_license,
            'accepts_host_standards' => $this->accepts_host_standards,
            'accepts_compensation_rules' => $this->accepts_compensation_rules,
            'license_number' => $this->license_number,
            'license_type' => $this->license_type,
            'license_issuer' => $this->license_issuer,
            'license_expiry' => $this->license_expiry?->format('Y-m-d'),
            'additional_documents' => $this->additional_documents,
        ];
    }
}
