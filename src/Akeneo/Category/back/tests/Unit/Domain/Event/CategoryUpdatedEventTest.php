<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\Event;

use Akeneo\Category\Domain\Event\CategoryUpdatedEvent;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryUpdatedEventTest extends TestCase
{
    private Category $category;

    protected function setUp(): void
    {
        $this->category = new Category(
            id: new CategoryId(1),
            code: new Code('socks'),
            templateUuid: null,
            labels: LabelCollection::fromArray(['en_US' => 'Socks']),
            parentId: new CategoryId(42),
        );
    }

    /**
     * UpdateCategoryVersionSubscriber builds a version snapshot out of the very category the command
     * handler saved. Handing back a copy (clone, re-hydration from the id, normalization) would let the
     * versioning read a state that was never persisted, so the transport must be transparent.
     */
    public function testItHandsBackTheExactCategoryInstanceItWasBuiltWith(): void
    {
        $sut = new CategoryUpdatedEvent($this->category);

        $this->assertSame($this->category, $sut->getCategory());
    }

    /**
     * Several listeners may read the event; each read must yield the same object, never a fresh one.
     */
    public function testItHandsBackTheSameCategoryInstanceOnEveryRead(): void
    {
        $sut = new CategoryUpdatedEvent($this->category);

        $this->assertSame($sut->getCategory(), $sut->getCategory());
    }

    /**
     * A domain event is a transport: no listener may swap its payload for the next listener in the chain.
     * The write is attempted through reflection, which is the only way to reach the private property, and
     * would succeed if the `readonly` modifier were dropped.
     */
    public function testItsCategoryCannotBeReplacedAfterConstruction(): void
    {
        $sut = new CategoryUpdatedEvent($this->category);
        $property = new \ReflectionProperty(CategoryUpdatedEvent::class, 'category');

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot modify readonly property');

        $property->setValue($sut, new Category(
            id: new CategoryId(2),
            code: new Code('shoes'),
            templateUuid: null,
        ));
    }
}
