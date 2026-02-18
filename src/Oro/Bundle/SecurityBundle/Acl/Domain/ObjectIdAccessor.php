<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;

/**
 * This class allows to get the class of a domain object
 */
class ObjectIdAccessor
{
    /**
     * Gets id for the given domain object
     *
     * @param  object                       $domainObject
     * @throws InvalidDomainObjectException
     */
    public function getId($domainObject): int|string
    {
        if ($domainObject instanceof DomainObjectInterface) {
            return $domainObject->getObjectIdentifier();
        } elseif (method_exists($domainObject, 'getUuid')
            // TODO TIP-987 Remove this when decoupling PublishedProduct from Enrichment
            && $domainObject::class !== 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct'
        ) {
            return $domainObject->getUuid()->toString();
        } elseif (method_exists($domainObject, 'getId')) {
            return $domainObject->getId();
        }
        throw new InvalidDomainObjectException(
            '$domainObject must either implement the DomainObjectInterface, or have a method named "getId".'
        );
    }
}
