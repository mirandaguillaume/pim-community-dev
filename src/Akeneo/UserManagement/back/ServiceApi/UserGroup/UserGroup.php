<?php

namespace Akeneo\UserManagement\ServiceApi\UserGroup;

class UserGroup
{
    final public const DEFAULT_NAME = 'All';

    public function __construct(
        private readonly int $id,
        private readonly string $label,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isDefault(): bool
    {
        return $this->label === self::DEFAULT_NAME;
    }
}
