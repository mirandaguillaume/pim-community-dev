<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Storage\Save;

use Akeneo\Category\Api\Command\UserIntents\SetImage;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\SetRichText;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Application\Storage\Save\CategorySaverRegistry;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryBaseSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTranslationsSaver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategorySaverRegistryTest extends TestCase
{
    public function test_it_returns_the_saver_related_to_a_user_intent(): void
    {
        $baseSaver = $this->createMock(CategoryBaseSaver::class);
        $translationsSaver = $this->createMock(CategoryTranslationsSaver::class);

        $baseSaver->method('getSupportedUserIntents')->willReturn([
            SetRichText::class,
            SetText::class,
            SetTextArea::class,
            SetImage::class,
        ]);
        $translationsSaver->method('getSupportedUserIntents')->willReturn([
            SetLabel::class,
        ]);

        $sut = new CategorySaverRegistry([$baseSaver, $translationsSaver]);

        $this->assertSame($baseSaver, $sut->fromUserIntent(SetText::class));
        $this->assertSame($baseSaver, $sut->fromUserIntent(SetTextArea::class));
        $this->assertSame($baseSaver, $sut->fromUserIntent(SetRichText::class));
        $this->assertSame($baseSaver, $sut->fromUserIntent(SetImage::class));
        $this->assertSame($translationsSaver, $sut->fromUserIntent(SetLabel::class));
    }

    public function test_it_should_throw_an_exception_when_the_same_user_intent_has_more_than_one_related_saver(): void
    {
        $baseSaver = $this->createMock(CategoryBaseSaver::class);
        $translationsSaver = $this->createMock(CategoryTranslationsSaver::class);

        $baseSaver->method('getSupportedUserIntents')->willReturn([
            SetText::class,
            SetTextArea::class,
            SetRichText::class,
            SetImage::class,
        ]);
        $translationsSaver->method('getSupportedUserIntents')->willReturn([SetLabel::class, SetText::class]);
        $this->expectException(\LogicException::class);
        new CategorySaverRegistry([$baseSaver, $translationsSaver]);
    }

    public function test_it_should_throw_an_exception_when_the_user_intent_has_no_related_saver(): void
    {
        $translationsSaver = $this->createMock(CategoryTranslationsSaver::class);
        $translationsSaver->method('getSupportedUserIntents')->willReturn([SetLabel::class]);
        $sut = new CategorySaverRegistry([$translationsSaver]);
        $this->expectException(\LogicException::class);
        $sut->fromUserIntent(SetText::class);
    }
}
