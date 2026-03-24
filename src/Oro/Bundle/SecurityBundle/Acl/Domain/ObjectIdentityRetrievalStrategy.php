<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Acl\Model\ObjectIdentityRetrievalStrategyInterface;

/**
 * Strategy to be used for retrieving object identities
 */
class ObjectIdentityRetrievalStrategy implements ObjectIdentityRetrievalStrategyInterface
{
    protected \Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory $objectIdentityFactory;

    /**
     * Constructor
     */
    public function __construct(ObjectIdentityFactory $objectIdentityFactory)
    {
        $this->objectIdentityFactory = $objectIdentityFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getObjectIdentity($domainObject)
    {
        try {
            return $this->objectIdentityFactory->get($domainObject);
        } catch (InvalidDomainObjectException) {
            return null;
        }
    }
}
