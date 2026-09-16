<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Storage\Save\Saver;

use Akeneo\Category\Api\Command\UserIntents\SetImage;
use Akeneo\Category\Api\Command\UserIntents\SetRichText;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryBase;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryBaseSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryBaseSaverTest extends TestCase
{
    /** @var string[] */
    private array $supportedUserIntents;

    private UpsertCategoryBase|MockObject $upsertCategoryBase;

    private CategoryBaseSaver $sut;

    protected function setUp(): void
    {
        $this->upsertCategoryBase = $this->createMock(UpsertCategoryBase::class);
        $this->supportedUserIntents = [
            SetText::class,
            SetTextArea::class,
            SetRichText::class,
            SetImage::class,
        ];
        $this->sut = new CategoryBaseSaver($this->upsertCategoryBase, $this->supportedUserIntents);
    }

    public function testItIsACategorySaver(): void
    {
        $this->assertInstanceOf(CategorySaver::class, $this->sut);
    }

    public function testItDelegatesTheSaveOfTheCategoryToTheUpsertBaseQuery(): void
    {
        $categoryModel = $this->aCategory();

        $this->upsertCategoryBase
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($categoryModel));

        $this->sut->save($categoryModel);
    }

    public function testItLetsTheUpsertQueryFailureBubbleUp(): void
    {
        $this->upsertCategoryBase
            ->method('execute')
            ->willThrowException(new \LogicException('Base data could not be saved'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Base data could not be saved');

        $this->sut->save($this->aCategory());
    }

    public function testItReturnsTheSupportedUserIntentsItWasConfiguredWithInOrder(): void
    {
        $this->assertSame($this->supportedUserIntents, $this->sut->getSupportedUserIntents());
    }

    public function testItSupportsNoUserIntentWhenNoneWasConfigured(): void
    {
        $sut = new CategoryBaseSaver($this->upsertCategoryBase, []);

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
