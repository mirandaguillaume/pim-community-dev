<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\LabelCollection;
use PHPUnit\Framework\TestCase;

class LabelCollectionTest extends TestCase
{
    public function test_it_creates_from_array(): void
    {
        $labels = LabelCollection::fromArray(['en_US' => 'Hello', 'fr_FR' => 'Bonjour']);
        $this->assertSame('Hello', $labels->getTranslation('en_US'));
        $this->assertSame('Bonjour', $labels->getTranslation('fr_FR'));
    }

    public function test_it_rejects_empty_locale_code(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('', 'some_label');
    }

    public function test_it_rejects_label_longer_than_255(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('en_US', str_repeat('a', 256));
    }

    public function test_it_accepts_label_of_exactly_255_chars(): void
    {
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('en_US', str_repeat('a', 255));
        $this->assertSame(str_repeat('a', 255), $labels->getTranslation('en_US'));
    }

    public function test_it_accepts_null_label(): void
    {
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('en_US', null);
        $this->assertNull($labels->getTranslation('en_US'));
    }

    public function test_it_normalizes_empty_string_to_null(): void
    {
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('en_US', '');
        $this->assertNull($labels->getTranslation('en_US'));
    }

    public function test_it_normalizes_zero_string_to_null(): void
    {
        $labels = LabelCollection::fromArray([]);
        $labels->setTranslation('en_US', '0');
        $this->assertNull($labels->getTranslation('en_US'));
    }

    public function test_it_returns_null_for_missing_locale(): void
    {
        $labels = LabelCollection::fromArray([]);
        $this->assertNull($labels->getTranslation('xx_XX'));
    }

    public function test_normalize_returns_all_labels(): void
    {
        $labels = LabelCollection::fromArray(['en_US' => 'Hello', 'fr_FR' => 'Bonjour']);
        $this->assertSame(['en_US' => 'Hello', 'fr_FR' => 'Bonjour'], $labels->normalize());
    }
}
