<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Builder;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Infrastructure\Builder\CategoryVersionBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryVersionBuilderTest extends TestCase
{
    private GetCategoryInterface|MockObject $getCategory;
    private CategoryVersionBuilder $sut;

    protected function setUp(): void
    {
        $this->getCategory = $this->createMock(GetCategoryInterface::class);
        $this->sut = new CategoryVersionBuilder($this->getCategory);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(CategoryVersionBuilder::class, $this->sut);
    }

    public function testItBuildsAVersionCarryingTheCategoryIdAsResourceId(): void
    {
        $categoryVersion = $this->sut->create($this->rootCategory(id: 42, code: 'master'));

        // The resource id is stored as a string in the versioning table.
        $this->assertSame('42', $categoryVersion->getResourceId());
        $this->assertSame('master', $categoryVersion->getSnapshot()['code']);
    }

    public function testItBuildsAVersionWithoutResourceIdWhenTheCategoryHasNoId(): void
    {
        $category = new Category(
            id: null,
            code: new Code('not_persisted_yet'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
        );

        $categoryVersion = $this->sut->create($category);

        $this->assertNull($categoryVersion->getResourceId());
        $this->assertSame('not_persisted_yet', $categoryVersion->getSnapshot()['code']);
    }

    public function testItBuildsTheWholeSnapshotOfAnEnrichedChildCategory(): void
    {
        $category = new Category(
            id: new CategoryId(5),
            code: new Code('print'),
            templateUuid: null,
            labels: LabelCollection::fromArray(['en_US' => 'print', 'fr_FR' => 'impression']),
            parentId: new CategoryId(2),
            rootId: new CategoryId(1),
            permissions: PermissionCollection::fromArray([
                PermissionCollection::VIEW => [['id' => 1, 'label' => 'All']],
                PermissionCollection::EDIT => [['id' => 2, 'label' => 'Redactor'], ['id' => 3, 'label' => 'Manager']],
                PermissionCollection::OWN => [['id' => 3, 'label' => 'Manager']],
            ]),
        );
        $this->getCategory->expects($this->once())->method('byId')->with(2)->willReturn($this->rootCategory(id: 2, code: 'categories'));

        $snapshot = $this->sut->buildSnapshot($category);

        // The versioning bundle diffs consecutive snapshots key by key: a missing or renamed key is a
        // silent history corruption, so the exact key set and its order are locked here.
        $this->assertSame(
            ['code', 'parent', 'updated', 'label-en_US', 'label-fr_FR', 'view_permission', 'edit_permission', 'own_permission'],
            array_keys($snapshot),
        );
        $this->assertSame('print', $snapshot['code']);
        $this->assertSame('categories', $snapshot['parent']);
        $this->assertSame('print', $snapshot['label-en_US']);
        $this->assertSame('impression', $snapshot['label-fr_FR']);
        $this->assertSame('All', $snapshot['view_permission']);
        $this->assertSame('Redactor,Manager', $snapshot['edit_permission']);
        $this->assertSame('Manager', $snapshot['own_permission']);
    }

    public function testItDoesNotResolveAnyParentForARootCategory(): void
    {
        $this->getCategory->expects($this->never())->method('byId');

        $snapshot = $this->sut->buildSnapshot($this->rootCategory(id: 1, code: 'master'));

        $this->assertNull($snapshot['parent']);
    }

    /**
     * Category::isRoot() is deliberately "belt and braces": a category pointing at itself as root is a
     * root even though it carries a parent id. No parent must be resolved in that case.
     */
    public function testItTreatsACategoryWhoseRootIsItselfAsARootCategory(): void
    {
        $categoryId = new CategoryId(1);
        $category = new Category(
            id: $categoryId,
            code: new Code('master'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            parentId: new CategoryId(9),
            rootId: $categoryId,
        );
        $this->getCategory->expects($this->never())->method('byId');

        $snapshot = $this->sut->buildSnapshot($category);

        $this->assertNull($snapshot['parent']);
    }

    /**
     * The parent code is resolved from the database, never read from Category::getParentCode(): a stale
     * parent code on the model must not leak into the history.
     */
    public function testItResolvesTheParentCodeFromTheRepositoryAndNotFromTheModel(): void
    {
        $category = new Category(
            id: new CategoryId(5),
            code: new Code('print'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            parentId: new CategoryId(2),
            parentCode: new Code('stale_parent_code'),
            rootId: new CategoryId(1),
        );
        $this->getCategory->expects($this->once())->method('byId')->with(2)->willReturn($this->rootCategory(id: 2, code: 'fresh_parent_code'));

        $snapshot = $this->sut->buildSnapshot($category);

        $this->assertSame('fresh_parent_code', $snapshot['parent']);
    }

    /**
     * Fallback of lines 49-52: when the resolved parent code is falsy ('', '0' or null) the builder
     * re-resolves the parent from the root id instead of writing an empty parent in the history.
     * '0' is the only falsy value a Code value object can hold (Code forbids the empty string).
     */
    public function testItFallsBackOnTheRootCodeWhenTheResolvedParentCodeIsFalsy(): void
    {
        $category = new Category(
            id: new CategoryId(5),
            code: new Code('print'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            parentId: new CategoryId(2),
            rootId: new CategoryId(1),
        );
        $this->getCategory->expects($this->exactly(2))->method('byId')->willReturnMap([
            [2, $this->rootCategory(id: 2, code: '0')],
            [1, $this->rootCategory(id: 1, code: 'master')],
        ]);

        $snapshot = $this->sut->buildSnapshot($category);

        $this->assertSame('master', $snapshot['parent']);
    }

    public function testItDoesNotFallBackOnTheRootWhenTheCategoryHasNoRootId(): void
    {
        $category = new Category(
            id: new CategoryId(5),
            code: new Code('print'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            parentId: new CategoryId(2),
            rootId: null,
        );
        $this->getCategory->expects($this->once())->method('byId')->with(2)->willReturn($this->rootCategory(id: 2, code: '0'));

        $snapshot = $this->sut->buildSnapshot($category);

        $this->assertSame('0', $snapshot['parent']);
    }

    public function testItAddsNoLabelKeyWhenTheCategoryHasNoTranslation(): void
    {
        $snapshot = $this->sut->buildSnapshot($this->rootCategory(id: 1, code: 'master'));

        $this->assertSame(['code', 'parent', 'updated'], array_keys($snapshot));
    }

    /**
     * LabelCollection normalises an empty translation to null: the key must still be emitted so that the
     * changeset records the removal of the label.
     */
    public function testItKeepsNullifiedTranslationsInTheSnapshot(): void
    {
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('master'),
            templateUuid: null,
            labels: LabelCollection::fromArray(['en_US' => 'master', 'fr_FR' => '']),
        );

        $snapshot = $this->sut->buildSnapshot($category);

        $this->assertSame(['code', 'parent', 'updated', 'label-en_US', 'label-fr_FR'], array_keys($snapshot));
        $this->assertSame('master', $snapshot['label-en_US']);
        $this->assertNull($snapshot['label-fr_FR']);
    }

    public function testItAddsNoPermissionKeyWhenTheCategoryCarriesNoPermissionCollection(): void
    {
        $snapshot = $this->sut->buildSnapshot($this->rootCategory(id: 1, code: 'master'));

        $this->assertArrayNotHasKey('view_permission', $snapshot);
        $this->assertArrayNotHasKey('edit_permission', $snapshot);
        $this->assertArrayNotHasKey('own_permission', $snapshot);
    }

    /**
     * Category::fromDatabase() always wraps the permissions in a PermissionCollection, even when the
     * database column is null (Community Edition). The three keys are then emitted as empty strings,
     * which is what VersionBuilder::hasPermission() sees when deciding to create a dedicated version.
     */
    public function testItEmitsEmptyPermissionsWhenThePermissionCollectionIsEmpty(): void
    {
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('master'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            permissions: PermissionCollection::fromArray(null),
        );

        $snapshot = $this->sut->buildSnapshot($category);

        $this->assertSame('', $snapshot['view_permission']);
        $this->assertSame('', $snapshot['edit_permission']);
        $this->assertSame('', $snapshot['own_permission']);
    }

    public function testItSetsTheUpdatedDateToTheCurrentUtcTime(): void
    {
        $snapshot = $this->sut->buildSnapshot($this->rootCategory(id: 1, code: 'master'));

        $updated = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $snapshot['updated']);

        $this->assertInstanceOf(\DateTimeImmutable::class, $updated, sprintf('"%s" is not a valid ATOM date', $snapshot['updated']));
        // The versioning history is read as UTC by the legacy version manager.
        $this->assertStringEndsWith('+00:00', $snapshot['updated']);
        $this->assertLessThanOrEqual(60, abs(time() - $updated->getTimestamp()));
    }

    private function rootCategory(int $id, string $code): Category
    {
        return new Category(
            id: new CategoryId($id),
            code: new Code($code),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
        );
    }
}
