<?php

namespace Akeneo\UserManagement\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository::class)]
#[ORM\Table(name: 'oro_access_group')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class Group implements GroupInterface, \Stringable
{
    final public const TYPE_DEFAULT = 'default';

    /** @var integer */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::SMALLINT)]
    protected $id;

    /** @var ArrayCollection */
    #[ORM\ManyToMany(targetEntity: \Akeneo\UserManagement\Component\Model\Role::class)]
    #[ORM\JoinTable(name: 'oro_user_access_group_role')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $roles;

    #[ORM\Column(name: 'default_permissions', type: Types::JSON, nullable: true)]
    protected ?array $defaultPermissions = null;
    #[ORM\Column(type: Types::STRING, length: 30, options: ['default' => 'default'])]
    protected string $type = self::TYPE_DEFAULT;

    /**
     * @param string $name [optional] Group name
     */
    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
        protected $name = '',
    ) {
        $this->roles = new ArrayCollection();
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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleLabelsAsString(): string
    {
        $labels = [];
        /** @var $role Role */
        foreach ($this->getRoles() as $role) {
            $labels[] = $role->getLabel();
        }

        return implode(', ', $labels);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRole($roleName): ?RoleInterface
    {
        /** @var $role Role */
        foreach ($this->getRoles() as $role) {
            if ($roleName == $role->getRole()) {
                return $role;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role): bool
    {
        if ($role instanceof Role) {
            $roleName = $role->getRole();
        } elseif (is_string($role)) {
            $roleName = $role;
        } else {
            throw new \InvalidArgumentException(
                sprintf('$role must be an instance of %s or a string', Group::class)
            );
        }

        return (bool) $this->getRole($roleName);
    }

    /**
     * {@inheritdoc}
     */
    public function addRole(RoleInterface $role): void
    {
        if (!$this->hasRole($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role): void
    {
        if ($role instanceof Role) {
            $roleObject = $role;
        } elseif (is_string($role)) {
            $roleObject = $this->getRole($role);
        } else {
            throw new \InvalidArgumentException(
                sprintf('$role must be an instance of %s or a string', Group::class)
            );
        }
        if ($roleObject) {
            $this->roles->removeElement($roleObject);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles($roles): void
    {
        if ($roles instanceof Collection) {
            $this->roles->clear();

            foreach ($roles as $role) {
                $this->addRole($role);
            }
        } elseif (is_array($roles)) {
            $this->roles = new ArrayCollection($roles);
        } else {
            throw new \InvalidArgumentException(
                '$roles must be an instance of Doctrine\Common\Collections\Collection or an array'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultPermissions(): ?array
    {
        return $this->defaultPermissions;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultPermissions(array $defaultPermissions): void
    {
        $this->defaultPermissions = $defaultPermissions;
    }

    public function setDefaultPermission(string $permission, bool $granted): void
    {
        if (null === $this->defaultPermissions) {
            $this->defaultPermissions = [];
        }

        $this->defaultPermissions[$permission] = $granted;
    }

    /**
     * Return the group name field
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
