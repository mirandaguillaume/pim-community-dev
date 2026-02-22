<?php

namespace Oro\Bundle\SecurityBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 *
 * TODO: Migrate @Annotation docblock to PHP 8 #[\Attribute] when Doctrine Annotations are fully replaced.
 */
class AclAncestor
{
    /**
     * @var string
     */
    private $id;

    /**
     * Constructor
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data)
    {
        $this->id = $data['value'] ?? null;
        if (empty($this->id) || str_contains((string) $this->id, ' ')) {
            throw new \InvalidArgumentException('ACL id must not be empty or contain blank spaces.');
        }
    }

    /**
     * Gets id of ACL annotation this ancestor is referred to
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return ['id' => $this->id];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
    }
}
