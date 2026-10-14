<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\EventSubscriber;

use Akeneo\Category\Domain\Event\CategoryUpdatedEvent;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\Version\CategoryVersion;
use Akeneo\Category\Infrastructure\Builder\CategoryVersionBuilder;
use Akeneo\Category\Infrastructure\EventSubscriber\UpdateCategoryVersionSubscriber;
use Akeneo\Tool\Bundle\VersioningBundle\ServiceApi\VersionBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateCategoryVersionSubscriberTest extends TestCase
{
    private VersionBuilder|MockObject $versionBuilder;
    private CategoryVersionBuilder|MockObject $categoryVersionBuilder;
    private UpdateCategoryVersionSubscriber $sut;

    protected function setUp(): void
    {
        $this->versionBuilder = $this->createMock(VersionBuilder::class);
        $this->categoryVersionBuilder = $this->createMock(CategoryVersionBuilder::class);
        $this->sut = new UpdateCategoryVersionSubscriber($this->versionBuilder, $this->categoryVersionBuilder);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(UpdateCategoryVersionSubscriber::class, $this->sut);
    }

    public function testItRelaysTheCategoryVersionToTheVersionBuilder(): void
    {
        $category = $this->createMock(Category::class);
        $snapshot = [
            'code' => 'print',
            'parent' => 'categories',
            'updated' => '2023-01-17T13:03:43+00:00',
            'label-en_US' => 'print',
        ];
        $categoryVersion = CategoryVersion::fromBuilder('42', $snapshot);

        // identicalTo: the version must be built from the very category carried by the event.
        $this->categoryVersionBuilder->expects($this->once())->method('create')->with($this->identicalTo($category))->willReturn($categoryVersion);
        $this->versionBuilder->expects($this->once())->method('buildVersionWithId')->with(
            '42',
            'Akeneo\Category\Infrastructure\Component\Model\Category',
            $snapshot,
        );

        $this->sut->updateCategoryVersion(new CategoryUpdatedEvent($category));
    }

    /**
     * A category without id must still reach the version builder with a null resource id: VersionBuilder
     * relies on that null to skip the dedicated permission version (GRF-671).
     */
    public function testItRelaysANullResourceIdWithoutCoercingIt(): void
    {
        $category = $this->createMock(Category::class);
        $categoryVersion = CategoryVersion::fromBuilder(null, ['code' => 'print', 'parent' => null, 'updated' => '2023-01-17T13:03:43+00:00']);

        $this->categoryVersionBuilder->method('create')->willReturn($categoryVersion);
        $this->versionBuilder->expects($this->once())->method('buildVersionWithId')->with(
            null,
            'Akeneo\Category\Infrastructure\Component\Model\Category',
            ['code' => 'print', 'parent' => null, 'updated' => '2023-01-17T13:03:43+00:00'],
        );

        $this->sut->updateCategoryVersion(new CategoryUpdatedEvent($category));
    }

    /**
     * The listener is wired by attribute only: nothing else references it. A rename of the method or of
     * the event class would silently stop versioning categories, so the wiring is asserted here.
     */
    public function testItIsWiredOnTheCategoryUpdatedEvent(): void
    {
        $attributes = new \ReflectionClass(UpdateCategoryVersionSubscriber::class)->getAttributes(AsEventListener::class);

        $this->assertCount(1, $attributes);

        $listener = $attributes[0]->newInstance();

        $this->assertSame(CategoryUpdatedEvent::class, $listener->event);
        $this->assertNotNull($listener->method);
        // method_exists() is case-insensitive, so it cannot see an attribute whose spelling has
        // drifted from the method it names -- which is how this listener was wired for a while.
        // Comparing against the declared names is what makes this assertion a real guard.
        $this->assertContains(
            $listener->method,
            get_class_methods(UpdateCategoryVersionSubscriber::class),
            sprintf(
                'The listener is wired on the method "%s", which the class does not declare under that exact spelling.',
                $listener->method,
            ),
        );
    }
}
