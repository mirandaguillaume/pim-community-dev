<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

/**
 * Extends the default Symfony ACL provider with support of a root ACL.
 * It means that the special ACL named "root" will be used in case when more sufficient ACL was not found.
 */
class RootBasedAclProvider implements AclProviderInterface
{
    /**
     * @var AclProviderInterface
     */
    protected $baseAclProvider;

    /**
     * @var ObjectIdentityFactory
     */
    protected $objectIdentityFactory = null;

    /**
     * Constructor
     */
    public function __construct(ObjectIdentityFactory $objectIdentityFactory)
    {
        $this->objectIdentityFactory = $objectIdentityFactory;
    }

    /**
     * Sets the base ACL provider
     */
    public function setBaseAclProvider(AclProviderInterface $provider)
    {
        $this->baseAclProvider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function findChildren(ObjectIdentityInterface $parentOid, $directChildrenOnly = false)
    {
        return $this->baseAclProvider->findChildren($parentOid, $directChildrenOnly);
    }

    /**
     * {@inheritdoc}
     */
    public function findAcl(ObjectIdentityInterface $oid, array $sids = [])
    {
        $rootOid = $this->objectIdentityFactory->root($oid);
        try {
            $acl = $this->getAcl($oid, $sids, $rootOid);
        } catch (AclNotFoundException $noAcl) {
            try {
                // Try to get ACL for underlying object
                $underlyingOid = $this->objectIdentityFactory->underlying($oid);
                $acl = $this->getAcl($underlyingOid, $sids, $rootOid);
            } catch (\Exception) {
                // Try to get ACL for root object
                try {
                    $this->baseAclProvider->cacheEmptyAcl($oid);

                    return $this->baseAclProvider->findAcl($rootOid, $sids);
                } catch (AclNotFoundException) {
                    throw new AclNotFoundException(
                        sprintf('There is no ACL for %s. The root ACL %s was not found as well.', $oid, $rootOid),
                        0,
                        $noAcl
                    );
                }
            }
        }

        return $acl;
    }

    /**
     * {@inheritdoc}
     */
    public function findAcls(array $oids, array $sids = [])
    {
        return $this->baseAclProvider->findAcls($oids, $sids);
    }

    /**
     * Get Acl based on given OID and Parent OID
     *
     * @return RootBasedAclWrapper|AclInterface
     */
    protected function getAcl(ObjectIdentityInterface $oid, array $sids, ObjectIdentityInterface $rootOid): \Oro\Bundle\SecurityBundle\Acl\Domain\RootBasedAclWrapper|\Symfony\Component\Security\Acl\Model\AclInterface
    {
        $acl = $this->baseAclProvider->findAcl($oid, $sids);
        try {
            $rootAcl = $this->baseAclProvider->findAcl($rootOid, $sids);
        } catch (AclNotFoundException) {
            return $acl;
        }

        return new RootBasedAclWrapper($acl, $rootAcl);
    }
}
