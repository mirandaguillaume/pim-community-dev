<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\SecurityBundle\Annotation\Loader;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Loader\AclAnnotationLoader;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage;
use Oro\Bundle\SecurityBundle\Unit\Annotation\Loader\Fixtures\ControllerWithClassAttribute;
use Oro\Bundle\SecurityBundle\Unit\Annotation\Loader\Fixtures\ControllerWithMethodAttribute;
use Oro\Bundle\SecurityBundle\Unit\Annotation\Loader\Fixtures\ControllerWithoutAttribute;
use PHPUnit\Framework\TestCase;

/**
 * Nothing covered this loader before, which is why it is covered now: it is the only thing that
 * turns the 91 #[AclAncestor] attributes on controller actions into ACL bindings, and it was just
 * rewritten from Doctrine's annotation reader to native reflection.
 */
class AclAnnotationLoaderTest extends TestCase
{
    private function loadFixtures(): AclAnnotationStorage
    {
        // An empty $subDirs makes the loader scan the given directories as they are, and
        // setBundleDirectories replaces the bundle discovery that needs a kernel.
        $loader = new AclAnnotationLoader([], []);
        $loader->setBundleDirectories([__DIR__ . '/Fixtures']);

        $storage = new AclAnnotationStorage();
        $loader->load($storage);

        return $storage;
    }

    public function test_it_binds_an_acl_to_a_method_carrying_the_attribute(): void
    {
        $storage = $this->loadFixtures();

        self::assertTrue($storage->isKnownMethod(ControllerWithMethodAttribute::class, 'indexAction'));
    }

    public function test_it_leaves_a_method_without_the_attribute_unbound(): void
    {
        $storage = $this->loadFixtures();

        self::assertFalse($storage->isKnownMethod(ControllerWithMethodAttribute::class, 'unguardedAction'));
    }

    public function test_it_binds_an_acl_to_a_class_carrying_the_attribute(): void
    {
        $storage = $this->loadFixtures();

        self::assertTrue($storage->isKnownClass(ControllerWithClassAttribute::class));
    }

    public function test_it_ignores_a_class_that_never_mentions_the_attribute(): void
    {
        $storage = $this->loadFixtures();

        self::assertFalse($storage->isKnownClass(ControllerWithoutAttribute::class));
    }

    /**
     * The production arrangement, which neither half demonstrates on its own: an acl.yml file
     * declares the ACL and the attribute binds a controller action to it. 157 ACLs are declared
     * that way across 15 acl.yml files, and the attribute is what reaches them.
     */
    public function test_an_acl_declared_elsewhere_is_reachable_through_the_binding(): void
    {
        $loader = new AclAnnotationLoader([], []);
        $loader->setBundleDirectories([__DIR__ . '/Fixtures']);

        $storage = new AclAnnotationStorage();
        $declared = new Acl(['id' => 'acl_bound_to_a_method', 'type' => 'action']);
        $storage->add($declared);

        $loader->load($storage);

        self::assertSame($declared, $storage->find(ControllerWithMethodAttribute::class, 'indexAction'));
    }

    public function test_it_rejects_an_acl_id_that_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AclAncestor('');
    }

    public function test_it_rejects_an_acl_id_containing_a_space(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AclAncestor('two words');
    }
}
