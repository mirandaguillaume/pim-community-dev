<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\LabelCollection;
use PHPUnit\Framework\TestCase;

class LabelCollectionTest extends TestCase
{
    public function testItCreatesFromArray(): void
    {
        $labels = LabelCollection::fromArray(['en_US' => 'Hello', 'fr_FR' => 'Bonjour']);
        $this->assertSame('Hello', $labels->getTranslation('en_US'));
        $this->assertSame('Bonjour', $labels->getTranslation('fr_FR'));
    }

    public function testItRejectsEmptyLocaleCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('', 'some_label');
    }

    public function testItRejectsLabelLongerThan255(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('en_US', str_repeat('a', 256));
    }

    public function testItAcceptsLabelOfExactly255Chars(): void
    {
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('en_US', str_repeat('a', 255));
        $this->assertSame(str_repeat('a', 255), $labels->getTranslation('en_US'));
    }

    public function testItAcceptsNullLabel(): void
    {
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('en_US', null);
        $this->assertNull($labels->getTranslation('en_US'));
    }

    public function testItNormalizesEmptyStringToNull(): void
    {
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('en_US', '');
        $this->assertNull($labels->getTranslation('en_US'));
    }

    public function testItNormalizesZeroStringToNull(): void
    {
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('en_US', '0');
        $this->assertNull($labels->getTranslation('en_US'));
    }

    public function testItReturnsNullForMissingLocale(): void
    {
        $labels = LabelCollection::fromArray([]);
        $this->assertNull($labels->getTranslation('xx_XX'));
    }

    public function testNormalizeReturnsAllLabels(): void
    {
        $labels = LabelCollection::fromArray(['en_US' => 'Hello', 'fr_FR' => 'Bonjour']);
        $this->assertSame(['en_US' => 'Hello', 'fr_FR' => 'Bonjour'], $labels->normalize());
    }

    public function testMergeAddsNewLabels(): void
    {
        $labels = LabelCollection::fromArray(['en_US' => 'Hello']);
        $labels->merge(LabelCollection::fromArray(['fr_FR' => 'Bonjour']));
        $this->assertSame('Bonjour', $labels->getTranslation('fr_FR'));
        $this->assertSame('Hello', $labels->getTranslation('en_US'));
    }

    public function testMergeOverridesExistingLabels(): void
    {
        $labels = LabelCollection::fromArray(['en_US' => 'Hello']);
        $labels->merge(LabelCollection::fromArray(['en_US' => 'Hi']));
        $this->assertSame('Hi', $labels->getTranslation('en_US'));
    }

    public function testMergeWithEmptyCollectionDoesNothing(): void
    {
        $labels = LabelCollection::fromArray(['en_US' => 'Hello']);
        $labels->merge(LabelCollection::fromArray([]));
        $this->assertSame(['en_US' => 'Hello'], $labels->normalize());
    }
}
