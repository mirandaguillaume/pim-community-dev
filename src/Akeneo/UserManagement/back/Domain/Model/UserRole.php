<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Domain\Model;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRole
{
    public function __construct(
        private readonly int $id,
        private readonly string $role,
        private readonly string $label,
        private readonly string $type,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public static function createFromDatabase(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            role: $data['role'],
            label: $data['label'],
            type: $data['type'],
        );
    }
}
