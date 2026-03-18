<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema as BaseSchema;
use Doctrine\DBAL\Schema\SchemaConfig;

/**
 * Patched ACL schema for DBAL 4 compatibility.
 *
 * symfony/security-acl v3.4.0 passes Table objects to addForeignKeyConstraint(),
 * but DBAL 4 requires string table names. This class replicates the original
 * Schema from security-acl with the fix applied.
 */
final class AclSchema extends BaseSchema
{
    protected array $options;

    public function __construct(array $options, ?Connection $connection = null)
    {
        $schemaConfig = null;
        if ($connection !== null) {
            $schemaManager = $connection->createSchemaManager();
            $schemaConfig = $schemaManager->createSchemaConfig();
        }

        parent::__construct([], [], $schemaConfig);

        $this->options = $options;

        $this->addClassTable();
        $this->addSecurityIdentitiesTable();
        $this->addObjectIdentitiesTable();
        $this->addObjectIdentityAncestorsTable();
        $this->addEntryTable();
    }

    public function addToSchema(BaseSchema $schema): void
    {
        foreach ($this->getTables() as $table) {
            $schema->_addTable($table);
        }

        foreach ($this->getSequences() as $sequence) {
            $schema->_addSequence($sequence);
        }
    }

    protected function addClassTable(): void
    {
        $table = $this->createTable($this->options['class_table_name']);
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('class_type', 'string', ['length' => 200]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['class_type']);
    }

    protected function addEntryTable(): void
    {
        $table = $this->createTable($this->options['entry_table_name']);

        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('class_id', 'integer', ['unsigned' => true]);
        $table->addColumn('object_identity_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('field_name', 'string', ['length' => 50, 'notnull' => false]);
        $table->addColumn('ace_order', 'smallint', ['unsigned' => true]);
        $table->addColumn('security_identity_id', 'integer', ['unsigned' => true]);
        $table->addColumn('mask', 'integer');
        $table->addColumn('granting', 'boolean');
        $table->addColumn('granting_strategy', 'string', ['length' => 30]);
        $table->addColumn('audit_success', 'boolean');
        $table->addColumn('audit_failure', 'boolean');

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['class_id', 'object_identity_id', 'field_name', 'ace_order']);
        $table->addIndex(['class_id', 'object_identity_id', 'security_identity_id']);

        $table->addForeignKeyConstraint($this->options['class_table_name'], ['class_id'], ['id'], ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE']);
        $table->addForeignKeyConstraint($this->options['oid_table_name'], ['object_identity_id'], ['id'], ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE']);
        $table->addForeignKeyConstraint($this->options['sid_table_name'], ['security_identity_id'], ['id'], ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE']);
    }

    protected function addObjectIdentitiesTable(): void
    {
        $table = $this->createTable($this->options['oid_table_name']);

        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('class_id', 'integer', ['unsigned' => true]);
        $table->addColumn('object_identifier', 'string', ['length' => 100]);
        $table->addColumn('parent_object_identity_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('entries_inheriting', 'boolean');

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['object_identifier', 'class_id']);
        $table->addIndex(['parent_object_identity_id']);

        $table->addForeignKeyConstraint($this->options['oid_table_name'], ['parent_object_identity_id'], ['id']);
    }

    protected function addObjectIdentityAncestorsTable(): void
    {
        $table = $this->createTable($this->options['oid_ancestors_table_name']);

        $table->addColumn('object_identity_id', 'integer', ['unsigned' => true]);
        $table->addColumn('ancestor_id', 'integer', ['unsigned' => true]);

        $table->setPrimaryKey(['object_identity_id', 'ancestor_id']);

        $table->addForeignKeyConstraint($this->options['oid_table_name'], ['object_identity_id'], ['id'], ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE']);
        $table->addForeignKeyConstraint($this->options['oid_table_name'], ['ancestor_id'], ['id'], ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE']);
    }

    protected function addSecurityIdentitiesTable(): void
    {
        $table = $this->createTable($this->options['sid_table_name']);

        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('identifier', 'string', ['length' => 200]);
        $table->addColumn('username', 'boolean');

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['identifier', 'username']);
    }
}
