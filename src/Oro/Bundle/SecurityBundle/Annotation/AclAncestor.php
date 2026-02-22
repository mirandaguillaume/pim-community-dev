<?php

namespace Oro\Bundle\SecurityBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
class AclAncestor implements \Serializable
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
     * {@inheritdoc}
     *
     * @deprecated Serializable interface is deprecated since PHP 8.1. Migrate to __serialize()/__unserialize() in a future PR.
     */
    public function serialize(): string
    {
        return serialize(
            [
                $this->id
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Serializable interface is deprecated since PHP 8.1. Migrate to __serialize()/__unserialize() in a future PR.
     */
    public function unserialize($serialized): void
    {
        [$this->id] = unserialize($serialized);
    }
}
