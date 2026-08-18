<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\Event;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Domain\Event\CategoryEditedEvent;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryEditedEventTest extends TestCase
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
     * UpdateCategoryController dispatches the event with the category it just read and enriched.
     * A copy would hide the additional properties added by FindCategoryAdditionalPropertiesRegistry.
     */
    public function testItHandsBackTheExactCategoryInstanceItWasBuiltWith(): void
    {
        $sut = new CategoryEditedEvent($this->category, []);

        $this->assertSame($this->category, $sut->getCategory());
    }

    /**
     * The user intents are the ordered instruction list applied to the category; listeners replay them,
     * so both the order and the very instances must survive the transport untouched.
     */
    public function testItPreservesTheOrderAndTheIdentityOfTheUserIntents(): void
    {
        $firstIntent = new SetLabel('en_US', 'socks');
        $secondIntent = new SetLabel('fr_FR', 'chaussettes');
        $thirdIntent = new SetLabel('de_DE', 'socken');

        $sut = new CategoryEditedEvent($this->category, [$firstIntent, $secondIntent, $thirdIntent]);

        $userIntents = $sut->getUserIntents();
        $this->assertCount(3, $userIntents);
        $this->assertSame($firstIntent, $userIntents[0]);
        $this->assertSame($secondIntent, $userIntents[1]);
        $this->assertSame($thirdIntent, $userIntents[2]);
    }

    /**
     * The list handed to the event is the output of CategoryEditAclFilter / CategoryEditUserIntentFilter.
     * Filtering with array_filter leaves holes in the keys; re-indexing them here (array_values) would
     * silently shift the positions listeners rely on.
     */
    public function testItPreservesTheGappedKeysLeftByTheUserIntentFilters(): void
    {
        $filteredIntents = [
            0 => new SetLabel('en_US', 'socks'),
            2 => new SetLabel('fr_FR', 'chaussettes'),
            5 => new SetLabel('de_DE', 'socken'),
        ];

        $sut = new CategoryEditedEvent($this->category, $filteredIntents);

        $this->assertSame([0, 2, 5], array_keys($sut->getUserIntents()));
        $this->assertSame($filteredIntents, $sut->getUserIntents());
    }

    /**
     * Filters are allowed to reject every intent (an edit request carrying only non-granted fields).
     * Listeners must then receive an empty array they can safely iterate, never null.
     */
    public function testItAcceptsAndHandsBackAnEmptyUserIntentList(): void
    {
        $sut = new CategoryEditedEvent($this->category, []);

        $this->assertSame([], $sut->getUserIntents());
    }

    /**
     * The event snapshots the list by value: mutating the source array after the event was built must not
     * reach the listeners. This would break if the payload were stored by reference or as a mutable
     * collection object.
     */
    public function testItSnapshotsTheUserIntentListAtConstructionTime(): void
    {
        $firstIntent = new SetLabel('en_US', 'socks');
        $intents = [$firstIntent];

        $sut = new CategoryEditedEvent($this->category, $intents);

        $intents[] = new SetLabel('fr_FR', 'chaussettes');
        unset($intents[0]);

        $this->assertSame([$firstIntent], $sut->getUserIntents());
    }

    /**
     * A domain event is a transport: no listener may swap the payload for the next listener in the chain.
     * The writes are attempted through reflection, the only way to reach the private properties, and would
     * succeed if the `readonly` modifiers were dropped.
     */
    public function testItsCategoryCannotBeReplacedAfterConstruction(): void
    {
        $sut = new CategoryEditedEvent($this->category, []);
        $property = new \ReflectionProperty(CategoryEditedEvent::class, 'category');

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot modify readonly property');

        $property->setValue($sut, new Category(
            id: new CategoryId(2),
            code: new Code('shoes'),
            templateUuid: null,
        ));
    }

    public function testItsUserIntentsCannotBeReplacedAfterConstruction(): void
    {
        $sut = new CategoryEditedEvent($this->category, [new SetLabel('en_US', 'socks')]);
        $property = new \ReflectionProperty(CategoryEditedEvent::class, 'userIntents');

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot modify readonly property');

        $property->setValue($sut, []);
    }
}
