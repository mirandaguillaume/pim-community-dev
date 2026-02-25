<?php

namespace Akeneo\UserManagement\Component\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository::class)]
#[ORM\Table(name: 'oro_access_role')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class Role implements RoleInterface, \Stringable
{
    final public const TYPE_DEFAULT = 'default';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::SMALLINT)]
    protected ?int $id = null;
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected ?string $label = null;
    #[ORM\Column(type: Types::STRING, length: 30, options: ['default' => 'default'])]
    protected string $type = self::TYPE_DEFAULT;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected ?string $role = null;

    public function __construct(?string $role = null)
    {
        $this->role = $role;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function setRole(?string $role): void
    {
        $this->role = $role;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Return the role name field
     */
    public function __toString(): string
    {
        return (string) $this->role;
    }
}
