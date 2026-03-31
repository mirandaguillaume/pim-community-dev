<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\Localization\Model\TranslationInterface;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

interface LabelTranslationInterface extends TranslationInterface
{
    public function setLabel(string $label): void;
}

class TranslatableUpdaterTest extends TestCase
{
    private TranslatableUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new TranslatableUpdater();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(TranslatableUpdater::class, $this->sut);
    }

    public function test_it_removes_the_translation_when_the_new_label_is_null(): void
    {
        $object = $this->createMock(TranslatableInterface::class);
        $translation = $this->createMock(LabelTranslationInterface::class);

        $object->expects($this->once())->method('setLocale')->with('en_US');
        $object->method('getTranslation')->willReturn($translation);
        $object->expects($this->once())->method('removeTranslation')->with($translation);
        $this->sut->update($object, ['en_US' => null]);
    }

    public function test_it_removes_the_translation_when_the_new_label_is_empty(): void
    {
        $object = $this->createMock(TranslatableInterface::class);
        $translation = $this->createMock(LabelTranslationInterface::class);

        $object->expects($this->once())->method('setLocale')->with('en_US');
        $object->method('getTranslation')->willReturn($translation);
        $object->expects($this->once())->method('removeTranslation')->with($translation);
        $this->sut->update($object, ['en_US' => '']);
    }

    public function test_it_set_a_new_label_when_the_new_label_is_not_empty(): void
    {
        $object = $this->createMock(TranslatableInterface::class);
        $translation = $this->createMock(LabelTranslationInterface::class);

        $object->expects($this->once())->method('setLocale')->with('en_US');
        $object->method('getTranslation')->willReturn($translation);
        $translation->expects($this->once())->method('setLabel')->with('foo');
        $this->sut->update($object, ['en_US' => 'foo']);
    }
}
