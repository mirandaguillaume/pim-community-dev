<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\SecurityBundle\Annotation;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use PHPUnit\Framework\TestCase;

/**
 * Acl is the value object behind the 157 ACLs declared across 15 acl.yml files, and its defaults
 * had no test: an acl.yml omitting `visible` or `order` relies on them, and nothing said what they
 * are. Added while removing doctrine/annotations, because covering the loader brought this
 * constructor into the mutation gate's scope without asserting anything about it.
 */
class AclTest extends TestCase
{
    public function test_it_reads_every_field_from_the_yaml_shape(): void
    {
        $acl = new Acl([
            'id' => 'pim_enrich_product_index',
            'type' => 'action',
            'permission' => 'EXECUTE',
            'class' => 'Acme\Product',
            'group_name' => 'pim',
            'label' => 'List products',
            'enabled_at_creation' => false,
            'order' => 42,
            'visible' => false,
        ]);

        self::assertSame('pim_enrich_product_index', $acl->getId());
        self::assertSame('action', $acl->getType());
        self::assertSame('EXECUTE', $acl->getPermission());
        self::assertSame('Acme\Product', $acl->getClass());
        self::assertSame('pim', $acl->getGroup());
        self::assertSame('List products', $acl->getLabel());
        self::assertFalse($acl->isEnabledAtCreation());
        self::assertSame(42, $acl->getOrder());
        self::assertFalse($acl->isVisible());
    }

    /**
     * The defaults an acl.yml entry gets when it declares only id and type, which most of them do.
     */
    public function test_it_defaults_every_optional_field(): void
    {
        $acl = new Acl(['id' => 'an_acl', 'type' => 'action']);

        self::assertSame('', $acl->getPermission());
        self::assertSame('', $acl->getClass());
        self::assertSame('', $acl->getGroup());
        self::assertSame('', $acl->getLabel());
        self::assertTrue($acl->isEnabledAtCreation());
        self::assertSame(0, $acl->getOrder());
        self::assertTrue($acl->isVisible());
    }

    public function test_it_rejects_a_missing_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Acl(['type' => 'action']);
    }

    public function test_it_rejects_an_id_containing_a_space(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Acl(['id' => 'two words', 'type' => 'action']);
    }

    public function test_it_rejects_a_missing_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/type must not be empty/i');

        new Acl(['id' => 'an_acl']);
    }
}
