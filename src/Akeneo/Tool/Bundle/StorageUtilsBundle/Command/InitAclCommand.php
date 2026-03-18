<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Command;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL\AclSchema;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\SchemaException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Replacement for Symfony\Bundle\AclBundle\Command\InitAclCommand
 * that accepts our patched AclSchema (DBAL 4 compatible) instead of the
 * final Symfony\Component\Security\Acl\Dbal\Schema.
 */
#[AsCommand(name: 'acl:init', description: 'Creates ACL tables in the database')]
final class InitAclCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AclSchema $schema,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->schema->addToSchema($this->connection->createSchemaManager()->introspectSchema());
        } catch (SchemaException $e) {
            $output->writeln('Aborting: ' . $e->getMessage());

            return Command::FAILURE;
        }

        foreach ($this->schema->toSql($this->connection->getDatabasePlatform()) as $sql) {
            $this->connection->executeStatement($sql);
        }

        $output->writeln('ACL tables have been initialized successfully.');

        return Command::SUCCESS;
    }
}
