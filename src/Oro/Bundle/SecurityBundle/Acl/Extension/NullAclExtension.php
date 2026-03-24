<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * This class implements "Null object" design pattern for AclExtensionInterface
 */
final class NullAclExtension implements AclExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($type, $id): never
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionKey(): never
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function validateMask($mask, $object, $permission = null): never
    {
        throw new InvalidAclMaskException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity($val): never
    {
        throw new InvalidDomainObjectException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskBuilder($permission): never
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getAllMaskBuilders(): never
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskPattern($mask): string
    {
        return 'NullAclExtension: ' . $mask;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasks($permission): null
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMasks($permission): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function adaptRootMask($rootMask, $object)
    {
        return $rootMask;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceBits($mask): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function removeServiceBits($mask)
    {
        return $mask;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessLevel($mask, $permission = null): int
    {
        return AccessLevel::UNKNOWN;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions($mask = null, $setOnly = false): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedPermissions(ObjectIdentity $oid): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultPermission(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getClasses(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function decideIsGranting($triggeredMask, $object, TokenInterface $securityToken): bool
    {
        return true;
    }
}
