<?php

namespace Oro\Bundle\SecurityBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 *
 * TODO: Migrate @Annotation docblock to PHP 8 #[\Attribute] when Doctrine Annotations are fully replaced.
 */
class Acl
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $permission;

    /**
     * @var string
     */
    private $group;

    /**
     * @var string
     */
    private $label;

    /** @var bool */
    private $isEnabledAtCreation = true;

    private int $order = 0;

    /**
     * true if the ACL must be visible in the UI. eg: the edit role permissions screen
     * ACL that are not visible still exist and can be managed by the code.
     */
    private bool $visible = true;

    /**
     * Constructor
     *
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(array $data = null)
    {
        if ($data === null) {
            return;
        }

        $this->id = $data['id'] ?? null;
        if (empty($this->id) || str_contains((string) $this->id, ' ')) {
            throw new \InvalidArgumentException('ACL id must not be empty or contain blank spaces.');
        }

        $this->type = $data['type'] ?? null;
        if (empty($this->type)) {
            throw new \InvalidArgumentException(sprintf('ACL type must not be empty. Id: %s.', $this->id));
        }

        $this->permission = $data['permission'] ?? '';
        $this->class = $data['class'] ?? '';
        $this->group = $data['group_name'] ?? '';
        $this->label = $data['label'] ?? '';
        $this->isEnabledAtCreation = $data['enabled_at_creation'] ?? true;
        $this->order = $data['order'] ?? 0;
        $this->visible = $data['visible'] ?? true;
    }

    /**
     * Gets id of this ACL annotation
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets ACL extension key
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets ACL class name
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Gets ACL permission name
     *
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Sets ACL permission name
     *
     * @param string $permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }

    /**
     * Gets ACL group name
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Gets ACL label name
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function isEnabledAtCreation(): bool
    {
        return $this->isEnabledAtCreation;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'class' => $this->class,
            'permission' => $this->permission,
            'group' => $this->group,
            'label' => $this->label,
            'order' => $this->order,
            'visible' => $this->visible,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->class = $data['class'];
        $this->permission = $data['permission'];
        $this->group = $data['group'];
        $this->label = $data['label'];
        $this->order = $data['order'];
        $this->visible = $data['visible'];
    }
}
