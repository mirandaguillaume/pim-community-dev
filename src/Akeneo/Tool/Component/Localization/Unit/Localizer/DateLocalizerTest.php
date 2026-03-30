<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization\Localizer;

use Akeneo\Tool\Component\Localization\Factory\DateFactory;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Akeneo\Tool\Component\Localization\Validator\Constraints\DateFormat;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Localization\Localizer\DateLocalizer;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DateLocalizerTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private DateFactory|MockObject $dateFactory;
    private DateLocalizer $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->dateFactory = $this->createMock(DateFactory::class);
        $this->sut = new DateLocalizer($this->validator, $this->dateFactory, ['pim_catalog_date']);
        $this->sut->userTimezone = date_default_timezone_get();
        date_default_timezone_set(self::TEST_TIMEZONE);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
    }

    public function test_it_is_a_localizer(): void
    {
        $this->assertInstanceOf(LocalizerInterface::class, $this->sut);
    }

    public function test_it_supports_attribute_type(): void
    {
        $this->assertSame(true, $this->sut->supports('pim_catalog_date'));
        $this->assertSame(false, $this->sut->supports('pim_catalog_number'));
    }

    public function test_it_validates_the_format(): void
    {
        $this->assertNull($this->sut->validate('28/10/2015', 'date', ['date_format' => 'd/m/Y']));
        $this->assertNull($this->sut->validate('01/10/2015', 'date', ['date_format' => 'd/m/Y']));
        $this->assertNull($this->sut->validate('2015/10/25', 'date', ['date_format' => 'Y/m/d']));
        $this->assertNull($this->sut->validate('2015/10/01', 'date', ['date_format' => 'Y/m/d']));
        $this->assertNull($this->sut->validate('2015-10-25', 'date', ['date_format' => 'Y-m-d']));
        $this->assertNull($this->sut->validate('2015-10-01', 'date', ['date_format' => 'Y-m-d']));
        $this->assertNull($this->sut->validate('', 'date', ['date_format' => 'Y-m-d']));
        $this->assertNull($this->sut->validate(null, 'date', ['date_format' => 'Y-m-d']));
        $this->assertNull($this->sut->validate(new \DateTime(), 'date', ['date_format' => 'Y-m-d']));
    }

    public function test_it_returns_a_constraint_if_the_format_is_not_valid(): void
    {
        $constraints = $this->createMock(ConstraintViolationListInterface::class);

        $constraints->method('count')->willReturn(1);
        $this->validator->method('validate')->with('28/10/2015', $this->anything())->willReturn($constraints);
        $this->assertSame($constraints, $this->sut->validate('28/10/2015', 'date', ['date_format' => 'd-m-Y']));
    }

    public function test_it_returns_a_constraint_if_date_format_does_not_respect_format_locale(): void
    {
        $constraints = $this->createMock(ConstraintViolationListInterface::class);
        $dateFormatter = $this->createMock(IntlDateFormatter::class);

        $dateConstraint = new DateFormat();
        $dateConstraint->dateFormat = 'dd/MM/yyyy';
        $dateConstraint->path = 'date';
        $constraints->method('count')->willReturn(1);
        $this->validator->method('validate')->with('28-10-2015', $dateConstraint)->willReturn($constraints);
        $this->dateFactory->method('create')->with(['locale' => 'fr_FR'])->willReturn($dateFormatter);
        $dateFormatter->method('getPattern')->willReturn('dd/MM/yyyy');
        $this->assertSame($constraints, $this->sut->validate('28-10-2015', 'date', ['locale' => 'fr_FR']));
    }

    public function test_it_delocalizes_with_date_format_option(): void
    {
        $dateFormatter = $this->createMock(IntlDateFormatter::class);

        $this->dateFactory->method('create')->with(['date_format' => 'dd/MM/yyyy'])->willReturn($dateFormatter);
        $dateFormatter->expects($this->once())->method('setLenient')->with(false);
        $dateFormatter->method('parse')->with('28/10/2015')->willReturn(1_445_986_800);
        $dateFormatter->method('format')->with(1_445_986_800)->willReturn('2015-10-28T00:00:00+01:00');
        $this->assertSame('2015-10-28T00:00:00+01:00', $this->sut->delocalize('28/10/2015', ['date_format' => 'dd/MM/yyyy']));
    }
}
