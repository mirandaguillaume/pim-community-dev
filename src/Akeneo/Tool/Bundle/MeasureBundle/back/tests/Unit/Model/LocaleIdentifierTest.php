<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LocaleIdentifier;
use PHPUnit\Framework\TestCase;

class LocaleIdentifierTest extends TestCase
{
    private LocaleIdentifier $sut;

    protected function setUp(): void
    {
        $this->sut = LocaleIdentifier::fromCode('en_US');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(LocaleIdentifier::class, $this->sut);
    }

    public function test_it_cannot_be_created_with_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->fromCode('');
    }

    public function test_it_can_normalize_itself(): void
    {
        $this->assertSame('en_US', $this->sut->normalize());
    }

    public function test_it_tells_if_it_is_equals_to_another_locale_reference(): void
    {
        $this->assertSame(true, $this->sut->equals(LocaleIdentifier::fromCode('en_US')));
        $this->assertSame(false, $this->sut->equals(LocaleIdentifier::fromCode('fr_FR')));
    }
}
