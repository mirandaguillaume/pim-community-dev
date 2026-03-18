<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * Replacement for the AclSchemaListener from symfony/acl-bundle.
 *
 * The original listener type-hints on the final Symfony\Component\Security\Acl\Dbal\Schema class,
 * which is incompatible with DBAL 4. This listener uses our patched AclSchema instead.
 */
class AclSchemaListener
{
    public function __construct(private readonly AclSchema $schema)
    {
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();
        $this->schema->addToSchema($schema);
    }
}
