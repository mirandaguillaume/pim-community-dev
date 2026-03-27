<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Storage\Save;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Storage\Save\CategorySaverProcessor;
use Akeneo\Category\Application\Storage\Save\CategorySaverRegistry;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategorySaverProcessorTest extends TestCase
{
    private CategorySaverRegistry|MockObject $categorySaverRegistry;
    private CategorySaverProcessor $sut;

    protected function setUp(): void
    {
        $this->categorySaverRegistry = $this->createMock(CategorySaverRegistry::class);
        $this->sut = new CategorySaverProcessor($this->categorySaverRegistry);
    }

    public function testItUsesTheCorrectSaversBasedOnUserIntentList(): void
    {
        $categoryTranslationsSaver = $this->createMock(CategoryTranslationsSaver::class);
        $categoryModel = $this->createMock(Category::class);

        $setLabelUserIntent = new SetLabel('en_US', 'socks');
        $this->categorySaverRegistry->method('fromUserIntent')->with($setLabelUserIntent::class)->willReturn($categoryTranslationsSaver);
        $categoryTranslationsSaver->expects($this->once())->method('save')->with($categoryModel);
        $this->sut->save($categoryModel, [$setLabelUserIntent]);
    }

    public function testItThrowsAnExceptionWhenTheSaverClassWasNotAddedIntoTheSaversList(): void
    {
        $categoryModel = $this->createMock(Category::class);

        $setLabelUserIntent = new SetLabel('en_US', 'socks');
        $this->categorySaverRegistry->method('fromUserIntent')->willThrowException(new \LogicException('No saver found'));
        $this->expectException(\LogicException::class);
        $this->sut->save($categoryModel, [$setLabelUserIntent]);
    }
}
