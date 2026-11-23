<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\SecurityBundle\Metadata;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage;
use PHPUnit\Framework\TestCase;

/**
 * The storage both loaders write into: acl.yml entries through add(), #[AclAncestor] attributes
 * through addAncestor(). It had no test, including for the duplicate-binding guard that is the one
 * place a contradictory configuration is caught.
 */
class AclAnnotationStorageTest extends TestCase
{
    public function test_an_added_acl_is_reachable_by_id(): void
    {
        $storage = new AclAnnotationStorage();
        $acl = new Acl(['id' => 'an_acl', 'type' => 'action']);

        $storage->add($acl);

        self::assertSame($acl, $storage->findById('an_acl'));
        self::assertNull($storage->findById('another_acl'));
    }

    public function test_adding_an_acl_with_a_class_binds_it(): void
    {
        $storage = new AclAnnotationStorage();
        $acl = new Acl(['id' => 'an_acl', 'type' => 'action']);

        $storage->add($acl, 'Acme\SomeController', 'indexAction');

        self::assertTrue($storage->isKnownClass('Acme\SomeController'));
        self::assertTrue($storage->isKnownMethod('Acme\SomeController', 'indexAction'));
        self::assertSame($acl, $storage->find('Acme\SomeController', 'indexAction'));
    }

    /**
     * An ancestor records a binding only: it never registers an annotation of its own, which is why
     * find() stays null until an acl.yml declares the ACL it points at.
     */
    public function test_an_ancestor_binds_without_declaring_the_acl(): void
    {
        $storage = new AclAnnotationStorage();

        $storage->addAncestor(new AclAncestor('an_acl'), 'Acme\SomeController', 'indexAction');

        self::assertTrue($storage->isKnownMethod('Acme\SomeController', 'indexAction'));
        self::assertNull($storage->find('Acme\SomeController', 'indexAction'));

        $acl = new Acl(['id' => 'an_acl', 'type' => 'action']);
        $storage->add($acl);

        self::assertSame($acl, $storage->find('Acme\SomeController', 'indexAction'));
    }

    public function test_it_rejects_two_different_acls_bound_to_the_same_method(): void
    {
        $storage = new AclAnnotationStorage();
        $storage->addBinding('first_acl', 'Acme\SomeController', 'indexAction');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/duplicate binding/i');

        $storage->addBinding('second_acl', 'Acme\SomeController', 'indexAction');
    }

    public function test_rebinding_the_same_acl_to_the_same_method_is_accepted(): void
    {
        $storage = new AclAnnotationStorage();
        $storage->addBinding('an_acl', 'Acme\SomeController', 'indexAction');
        $storage->addBinding('an_acl', 'Acme\SomeController', 'indexAction');

        self::assertTrue($storage->isKnownMethod('Acme\SomeController', 'indexAction'));
    }

    public function test_it_refuses_an_empty_class(): void
    {
        $storage = new AclAnnotationStorage();

        $this->expectException(\InvalidArgumentException::class);

        $storage->addBinding('an_acl', '');
    }
}
