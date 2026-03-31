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

    /** @var ConstraintViolationListInterface|null */
    private ?ConstraintViolationListInterface $overrideValidation = null;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->numberFactory = $this->createMock(NumberFactory::class);
        $this->sut = new NumberLocalizer($this->validator, $this->numberFactory, ['pim_catalog_number']);
        $this->overrideValidation = null;
        $this->validator->method('validate')->willReturnCallback(
            function (mixed $value, mixed $constraint = null) {
                if ($this->overrideValidation !== null) {
                    return $this->overrideValidation;
                }
                return new ConstraintViolationList();
            }
        );
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

    public function test_validate_returns_null_for_null(): void
    {
        $this->assertNull($this->sut->validate(null, 'number', ['decimal_separator' => '.']));
    }

    public function test_validate_returns_null_for_empty_string(): void
    {
        $this->assertNull($this->sut->validate('', 'number', ['decimal_separator' => '.']));
    }

    public function test_validate_returns_null_for_int(): void
    {
        $this->assertNull($this->sut->validate(42, 'number', ['decimal_separator' => '.']));
    }

    public function test_validate_returns_null_for_float(): void
    {
        $this->assertNull($this->sut->validate(3.14, 'number', ['decimal_separator' => '.']));
    }

    public function test_it_returns_a_constraint_if_the_decimal_separator_is_not_valid(): void
    {
        $constraints = $this->createMock(ConstraintViolationListInterface::class);
        $constraints->method('count')->willReturn(1);
        $this->overrideValidation = $constraints;
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
        $result = $this->sut->localize('-10.4', $options);
        // Must use ASCII minus (-), not Unicode minus (−)
        $this->assertSame('-10,40', $result);
        $this->assertStringStartsWith('-', $result);
        $this->assertSame(ord('-'), ord($result[0]));
    }

    public function test_localize_without_locale_replaces_decimal_separator(): void
    {
        $result = $this->sut->localize('10.5', ['decimal_separator' => ',']);
        $this->assertSame('10,5', $result);
    }

    public function test_localize_integer_without_locale_returns_as_is(): void
    {
        $result = $this->sut->localize('10', ['decimal_separator' => ',']);
        $this->assertSame('10', $result);
    }

    public function test_localize_non_numeric_returns_as_is(): void
    {
        $result = $this->sut->localize('abc', ['decimal_separator' => ',']);
        $this->assertSame('abc', $result);
    }

    public function test_localize_removes_grouping_separator(): void
    {
        $options = ['locale' => 'en_US'];
        $numberFormatter = new \NumberFormatter('en_US', \NumberFormatter::DECIMAL);
        $this->numberFactory->method('create')->with($options)->willReturn($numberFormatter);
        // Without our setSymbol override, 1234.5 would be "1,234.5"
        $result = $this->sut->localize('1234.5', $options);
        $this->assertStringNotContainsString(',', $result);
    }

    public function test_localize_integer_has_no_fraction_digits(): void
    {
        $options = ['locale' => 'en_US'];
        $numberFormatter = new \NumberFormatter('en_US', \NumberFormatter::DECIMAL);
        $this->numberFactory->method('create')->with($options)->willReturn($numberFormatter);
        // floor(10) == 10, so MIN_FRACTION_DIGITS should NOT be set to 2
        $result = $this->sut->localize('10', $options);
        $this->assertSame('10', $result);
    }

    public function test_localize_decimal_has_at_least_two_fraction_digits(): void
    {
        $options = ['locale' => 'en_US'];
        $numberFormatter = new \NumberFormatter('en_US', \NumberFormatter::DECIMAL);
        $this->numberFactory->method('create')->with($options)->willReturn($numberFormatter);
        // floor(10.5) != 10.5, so MIN_FRACTION_DIGITS=2 kicks in
        $result = $this->sut->localize('10.5', $options);
        $this->assertSame('10.50', $result);
    }

    public function test_it_returns_all_decimals_for_a_number_with_very_long_decimals(): void
    {
        $options = ['locale' => 'sv_SE'];
        $numberFormatter = new \NumberFormatter('sv_SE', \NumberFormatter::DECIMAL);
        $this->numberFactory->method('create')->with($options)->willReturn($numberFormatter);
        $this->assertSame('-10,478978070878798', $this->sut->localize('-10.4789780708787980', $options));
    }

    public function test_delocalize_numeric_string_without_decimal_returns_as_is(): void
    {
        // Kills ReturnRemoval on line 64 (return $number when no decimal found)
        $this->assertSame('42', $this->sut->delocalize('42', ['decimal_separator' => ',']));
    }

    public function test_delocalize_casts_to_string(): void
    {
        // Kills CastString mutations on lines 85 and 88
        $result = $this->sut->delocalize('10,5', ['decimal_separator' => '.']);
        $this->assertIsString($result);
        $this->assertSame('10.5', $result);

        $result2 = $this->sut->delocalize('gruik', ['decimal_separator' => '.']);
        $this->assertIsString($result2);
    }

    public function test_validate_with_locale_gets_decimal_from_formatter(): void
    {
        $numberFormatter = new \NumberFormatter('fr_FR', \NumberFormatter::DECIMAL);
        $this->numberFactory->method('create')->willReturn($numberFormatter);
        $result = $this->sut->validate('10,5', 'number', ['locale' => 'fr_FR']);
        $this->assertNull($result);
    }
}
