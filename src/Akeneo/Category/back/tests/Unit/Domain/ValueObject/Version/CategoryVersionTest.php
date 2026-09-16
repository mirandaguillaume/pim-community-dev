<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Version;

use Akeneo\Category\Domain\ValueObject\Version\CategoryVersion;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryVersionTest extends TestCase
{
    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(CategoryVersion::class, CategoryVersion::fromBuilder('1', ['code' => 'print', 'parent' => null, 'updated' => '2023-01-17T13:03:43+00:00']));
    }

    /**
     * The resource name drives the `resource_name` column of the versioning table: every row already
     * stored in database uses this exact literal, so changing it silently breaks the category history.
     * It is asserted literally on purpose - never derive it from ::class.
     */
    public function testItExposesTheLegacyCategoryResourceNameLiterally(): void
    {
        $categoryVersion = CategoryVersion::fromBuilder('42', ['code' => 'print', 'parent' => null, 'updated' => '2023-01-17T13:03:43+00:00']);

        $this->assertSame('Akeneo\Category\Infrastructure\Component\Model\Category', $categoryVersion->getResourceName());
        $this->assertSame('Akeneo\Category\Infrastructure\Component\Model\Category', CategoryVersion::CATEGORY_VERSION_RESOURCE_NAME);
    }

    public function testItKeepsTheSnapshotUntouchedIncludingItsKeyOrder(): void
    {
        $snapshot = [
            'code' => 'print',
            'parent' => 'categories',
            'updated' => '2023-01-17T13:03:43+00:00',
            'label-en_US' => 'print',
            'label-fr_FR' => 'impression',
            'view_permission' => 'All',
        ];

        $categoryVersion = CategoryVersion::fromBuilder('12', $snapshot);

        // assertSame on arrays is order and type sensitive: the changeset computed downstream by the
        // versioning bundle diffs this array, so neither the order nor the types may drift.
        $this->assertSame($snapshot, $categoryVersion->getSnapshot());
        $this->assertSame('12', $categoryVersion->getResourceId());
    }

    /**
     * A null resource id means "the category has no id yet"; VersionBuilder relies on it to decide
     * whether a dedicated permission version has to be created (GRF-671), so it must not be coerced.
     */
    public function testItAcceptsANullResourceId(): void
    {
        $categoryVersion = CategoryVersion::fromBuilder(null, ['code' => 'print', 'parent' => null, 'updated' => '2023-01-17T13:03:43+00:00']);

        $this->assertNull($categoryVersion->getResourceId());
    }

    public function testItIsBuiltThroughTheNamedConstructorOnly(): void
    {
        $constructor = new \ReflectionClass(CategoryVersion::class)->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());
    }
}
