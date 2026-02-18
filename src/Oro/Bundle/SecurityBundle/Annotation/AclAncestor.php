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
     */
    public function serialize()
    {
        return serialize(
            [
                $this->id
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        [$this->id] = unserialize($serialized);
    }
}
