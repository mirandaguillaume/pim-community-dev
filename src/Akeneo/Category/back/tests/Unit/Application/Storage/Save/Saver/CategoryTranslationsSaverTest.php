<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Storage\Save\Saver;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryTranslations;
use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\UpdateCategoryUpdatedDate;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTranslationsSaverTest extends TestCase
{
    /** @var string[] */
    private array $supportedUserIntents;

    private UpsertCategoryTranslations|MockObject $upsertCategoryTranslations;

    private UpdateCategoryUpdatedDate|MockObject $updateCategoryUpdatedDate;

    private CategoryTranslationsSaver $sut;

    protected function setUp(): void
    {
        $this->upsertCategoryTranslations = $this->createMock(UpsertCategoryTranslations::class);
        $this->updateCategoryUpdatedDate = $this->createMock(UpdateCategoryUpdatedDate::class);
        $this->supportedUserIntents = [SetLabel::class];
        $this->sut = new CategoryTranslationsSaver(
            $this->upsertCategoryTranslations,
            $this->updateCategoryUpdatedDate,
            $this->supportedUserIntents,
        );
    }

    public function testItIsACategorySaver(): void
    {
        $this->assertInstanceOf(CategorySaver::class, $this->sut);
    }

    public function testItUpsertsTheTranslationsThenTouchesTheCategoryUpdatedDate(): void
    {
        $categoryModel = $this->aCategory();
        $calls = [];

        $this->upsertCategoryTranslations
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($categoryModel))
            ->willReturnCallback(function () use (&$calls): void {
                $calls[] = 'upsert_translations';
            });

        $this->updateCategoryUpdatedDate
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo('socks'))
            ->willReturnCallback(function () use (&$calls): void {
                $calls[] = 'update_updated_date';
            });

        $this->sut->save($categoryModel);

        $this->assertSame(['upsert_translations', 'update_updated_date'], $calls);
    }

    public function testItDoesNotTouchTheUpdatedDateWhenTheTranslationsUpsertFails(): void
    {
        $this->upsertCategoryTranslations
            ->method('execute')
            ->willThrowException(new \LogicException('Translations could not be saved'));

        $this->updateCategoryUpdatedDate->expects($this->never())->method('execute');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Translations could not be saved');

        $this->sut->save($this->aCategory());
    }

    public function testItLetsTheUpdatedDateFailureBubbleUp(): void
    {
        $this->updateCategoryUpdatedDate
            ->method('execute')
            ->willThrowException(new \LogicException('Updated date could not be saved'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Updated date could not be saved');

        $this->sut->save($this->aCategory());
    }

    public function testItReturnsTheSupportedUserIntentsItWasConfiguredWithInOrder(): void
    {
        $this->assertSame($this->supportedUserIntents, $this->sut->getSupportedUserIntents());
    }

    public function testItSupportsNoUserIntentWhenNoneWasConfigured(): void
    {
        $sut = new CategoryTranslationsSaver(
            $this->upsertCategoryTranslations,
            $this->updateCategoryUpdatedDate,
            [],
        );

        $this->assertSame([], $sut->getSupportedUserIntents());
    }

    private function aCategory(): Category
    {
        return new Category(
            id: new CategoryId(1),
            code: new Code('socks'),
            templateUuid: null,
            labels: LabelCollection::fromArray(['en_US' => 'Socks']),
        );
    }
}
