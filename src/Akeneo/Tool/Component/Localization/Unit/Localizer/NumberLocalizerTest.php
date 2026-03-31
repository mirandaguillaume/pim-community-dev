<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization\Localizer;

use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Akeneo\Tool\Component\Localization\Localizer\NumberLocalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NumberLocalizerTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private NumberFactory|MockObject $numberFactory;
    private NumberLocalizer $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->numberFactory = $this->createMock(NumberFactory::class);
        $this->sut = new NumberLocalizer($this->validator, $this->numberFactory, ['pim_catalog_number']);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
    }

    public function test_it_is_a_localizer(): void
    {
        $this->assertInstanceOf(LocalizerInterface::class, $this->sut);
    }

    public function test_it_supports_attribute_type(): void
    {
        $this->assertSame(true, $this->sut->supports('pim_catalog_number'));
        $this->assertSame(false, $this->sut->supports('pim_catalog_metric'));
        $this->assertSame(false, $this->sut->supports('pim_catalog_price_collection'));
    }

    public function test_it_valids_the_format(): void
    {
        $this->assertNull($this->sut->validate('10.05', 'number', ['decimal_separator' => '.']));
        $this->assertNull($this->sut->validate('-10.05', 'number', ['decimal_separator' => '.']));
        $this->assertNull($this->sut->validate('10', 'number', ['decimal_separator' => '.']));
        $this->assertNull($this->sut->validate('-10', 'number', ['decimal_separator' => '.']));
        $this->assertNull($this->sut->validate(10, 'number', ['decimal_separator' => '.']));
        $this->assertNull($this->sut->validate(10.0585, 'number', ['decimal_separator' => '.']));
        $this->assertNull($this->sut->validate(' 10.05 ', 'number', ['decimal_separator' => '.']));
        $this->assertNull($this->sut->validate(null, 'number', ['decimal_separator' => '.']));
        $this->assertNull($this->sut->validate('', 'number', ['decimal_separator' => '.']));
        $this->assertNull($this->sut->validate('0', 'number', ['decimal_separator' => '.']));
        $this->assertNull($this->sut->validate(0, 'number', ['decimal_separator' => '.']));
    }

    public function test_it_returns_a_constraint_if_the_decimal_separator_is_not_valid(): void
    {
        $constraints = $this->createMock(ConstraintViolationListInterface::class);

        $constraints->method('count')->willReturn(1);
        $this->validator->method('validate')->with('10.00', $this->anything())->willReturn($constraints);
        $this->assertSame($constraints, $this->sut->validate('10.00', 'number', ['decimal_separator' => ',']));
    }

    public function test_it_convert_comma_to_dot_separator(): void
    {
        $this->assertSame('10.05', $this->sut->delocalize('10,05', ['decimal_separator' => '.']));
        $this->assertSame('-10.05', $this->sut->delocalize('-10,05', ['decimal_separator' => '.']));
        $this->assertSame('10', $this->sut->delocalize('10', ['decimal_separator' => '.']));
        $this->assertSame('-10', $this->sut->delocalize('-10', ['decimal_separator' => '.']));
        $this->assertSame(10, $this->sut->delocalize(10, ['decimal_separator' => '.']));
        $this->assertSame('10.0585', $this->sut->delocalize(10.0585, ['decimal_separator' => '.']));
        $this->assertSame(' 10.05 ', $this->sut->delocalize(' 10,05 ', ['decimal_separator' => '.']));
        $this->assertNull($this->sut->delocalize(null, ['decimal_separator' => '.']));
        $this->assertNull($this->sut->delocalize('', ['decimal_separator' => '.']));
        $this->assertSame(0, $this->sut->delocalize(0, ['decimal_separator' => '.']));
        $this->assertSame('0', $this->sut->delocalize('0', ['decimal_separator' => '.']));
        $this->assertSame('10.00', $this->sut->delocalize('10,00', []));
        $this->assertSame('10.00', $this->sut->delocalize('10,00', ['decimal_separator' => null]));
        $this->assertSame('10.00', $this->sut->delocalize('10,00', ['decimal_separator' => '']));
        $this->assertSame('gruik', $this->sut->delocalize('gruik', ['decimal_separator' => '']));
    }

    public function test_it_returns_always_a_negative_symbol_for_negative_number(): void
    {
        $options = ['locale' => 'sv_SE'];
        $numberFormatter = new \NumberFormatter('sv_SE', \NumberFormatter::DECIMAL);
        $this->numberFactory->method('create')->with($options)->willReturn($numberFormatter);
        $this->assertSame('-10,40', $this->sut->localize('-10.4', $options));
    }

    public function test_it_returns_all_decimals_for_a_number_with_very_long_decimals(): void
    {
        $options = ['locale' => 'sv_SE'];
        $numberFormatter = new \NumberFormatter('sv_SE', \NumberFormatter::DECIMAL);
        $this->numberFactory->method('create')->with($options)->willReturn($numberFormatter);
        $this->assertSame('-10,478978070878798', $this->sut->localize('-10.4789780708787980', $options));
    }
}
