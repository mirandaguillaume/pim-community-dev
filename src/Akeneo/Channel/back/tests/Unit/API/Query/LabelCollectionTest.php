<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\API\Query;

use Akeneo\Channel\API\Query\LabelCollection;
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

    public function test_it_is_constructed_from_an_array_of_labels_and_returns_the_translated_label(): void
    {
        $this->assertSame('A US label', $this->sut->getLabel('en_US'));
        $this->assertSame('Un label français', $this->sut->getLabel('fr_FR'));
    }

    public function test_it_returns_null_if_the_locale_is_not_found(): void
    {
        $this->assertNull($this->sut->getLabel('ru_RU'));
    }

    public function test_it_tells_if_it_has_label(): void
    {
        $this->assertSame(true, $this->sut->hasLabel('en_US'));
        $this->assertSame(false, $this->sut->hasLabel('ru_RU'));
    }
}
