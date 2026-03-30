<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PHPUnit\Framework\TestCase;

class LocaleCollectionTest extends TestCase
{
    private LocaleCollection $sut;

    protected function setUp(): void
    {
    }

    public function test_it_throws_an_exception_if_constructed_with_empty_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LocaleCollection([]);
    }

    public function test_it_can_be_constructed_with_an_array_of_locale_codes(): void
    {
        $aLocaleCode = new LocaleCode('en_US');
        $this->sut = new LocaleCollection([$aLocaleCode]);
        $this->assertEquals(new \ArrayIterator(['en_US' => $aLocaleCode]), $this->sut->getIterator());
    }

    public function test_it_cannot_be_constructed_with_something_else_than_an_array_of_locale_codes(): void
    {
        $aFamilyCode = new FamilyCode('accessories');
        $this->expectException(\TypeError::class);
        new LocaleCollection([$aFamilyCode]);
    }

    public function test_it_adds_locale_to_the_collection(): void
    {
        $aLocaleCode = new LocaleCode('en_GB');
        $anotherLocaleCode = new LocaleCode('en_US');
        $this->sut = new LocaleCollection([$aLocaleCode]);
        $this->sut->add($anotherLocaleCode);
        $this->assertEquals(new \ArrayIterator([
                    'en_GB' => $aLocaleCode,
                    'en_US' => $anotherLocaleCode,
                ]), $this->sut->getIterator());
    }

    public function test_it_deduplicate_locale(): void
    {
        $aLocaleCode = new LocaleCode('en_US');
        $anotherLocaleCode = new LocaleCode('en_US');
        $this->sut = new LocaleCollection([$aLocaleCode]);
        $this->sut->add($anotherLocaleCode);
        $this->assertEquals(new \ArrayIterator([
                    'en_US' => new LocaleCode('en_US'),
                ]), $this->sut->getIterator());
    }
}
