<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Localization\Factory\DateFactory;

class DateFactoryTest extends TestCase
{
    private DateFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new DateFactory(['en_US' => 'm/d/Y', 'fr_FR' => 'dd/MM/yyyy']);
    }

    public function test_it_returns_intl_formatter(): void
    {
        $this->sut->create([])->shouldReturnAnInstanceOf(\IntlDateFormatter::class);
    }

    public function test_it_creates_a_date_with_intl_format(): void
    {
        $options = ['locale' => 'fr_FR'];
        $this->assertSame('dd/MM/yyyy', $this->sut->create($options)->getPattern());
    }

    public function test_it_creates_a_date_with_defined_format(): void
    {
        $options = ['locale' => 'fr_FR', 'date_format' => 'd/M/yy'];
        $this->assertSame('d/M/yy', $this->sut->create($options)->getPattern());
    }

    public function test_it_replaces_2_digit_years_by_4_digit_when_the_format_is_not_specified(): void
    {
        $formatter = $this->createMock(IntlDateFormatter::class);

        $options = ['locale' => 'en_AU'];
        $this->assertSame('d/M/yy', $this->sut->create($options, false)->getPattern());
        $this->assertSame('d/M/yyyy', $this->sut->create($options, true)->getPattern());
    }
}
