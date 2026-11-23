<?php

namespace Oro\Bundle\SecurityBundle\Annotation;

/**
 * Binds a controller action to an ACL declared in an acl.yml file.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class AclAncestor
{
    // Not readonly: __unserialize assigns it, and a readonly property cannot be written twice.
    private string $id;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(string $id)
    {
        if ('' === $id || str_contains($id, ' ')) {
            throw new \InvalidArgumentException('ACL id must not be empty or contain blank spaces.');
        }

        $this->id = $id;
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
