<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LocaleIdentifier;
use PHPUnit\Framework\TestCase;

class LabelCollectionTest extends TestCase
{
    private LabelCollection $sut;

    protected function setUp(): void
    {
        $this->sut = LabelCollection::fromArray(['en_US' => 'A US label', 'fr_FR' => 'Un label français']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(LabelCollection::class, $this->sut);
    }

    public function test_it_cannot_create_a_label_collection_if_keys_are_not_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LabelCollection::fromArray(['label1', 'label2']);
    }

    public function test_it_cannot_create_a_label_collection_if_values_are_an_integer(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LabelCollection::fromArray(['en_US' => 1]);
    }

    public function test_it_cannot_create_a_label_collection_if_keys_are_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LabelCollection::fromArray(['' => 'Book']);
    }

    public function test_it_is_constructed_from_an_array_of_labels_and_returns_the_translated_label(): void
    {
        $this->assertSame('A US label', $this->sut->getLabel('en_US'));
        $this->assertSame('Un label français', $this->sut->getLabel('fr_FR'));
    }

    public function test_it_returns_null_if_the_locale_is_not_found(): void
    {
        $this->assertNull($this->sut->getLabel('ru_RU'));
    }

    public function test_it_does_not_add_empty_labels_to_the_collection(): void
    {
        $this->sut = LabelCollection::fromArray(['en_US' => 'A US label', 'fr_FR' => '']);
        $this->assertSame('A US label', $this->sut->getLabel('en_US'));
        $this->assertNull($this->sut->getLabel('fr_FR'));
        $this->assertSame(['en_US' => 'A US label'], $this->sut->normalize());
    }

    public function test_it_tells_if_it_has_label(): void
    {
        $this->assertSame(true, $this->sut->hasLabel('en_US'));
        $this->assertSame(false, $this->sut->hasLabel('ru_RU'));
    }

    public function test_it_returns_the_locale_codes_it_has_translation_for(): void
    {
        $this->assertSame(['en_US', 'fr_FR'], $this->sut->getLocaleCodes());
    }

    public function test_it_can_normalize_itself(): void
    {
        $this->assertSame(['en_US' => 'A US label', 'fr_FR' => 'Un label français'], $this->sut->normalize());
    }

    public function test_it_filters_the_labels_by_locale_identifiers(): void
    {
        $this->sut = LabelCollection::fromArray([
                    'en_US' => 'A US label',
                    'fr_FR' => 'Un label français',
                    'de_DE' => 'Eine deutsche label',
                ]);
        $this->assertEquals(LabelCollection::fromArray([
                    'en_US' => 'A US label',
                    'de_DE' => 'Eine deutsche label',
                ]), $this->sut->filterByLocaleIdentifiers([
                    LocaleIdentifier::fromCode('en_US'),
                    LocaleIdentifier::fromCode('de_DE'),
                ]));
    }
}
