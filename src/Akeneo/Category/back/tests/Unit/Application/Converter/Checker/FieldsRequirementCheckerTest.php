<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Converter\Checker;

use Akeneo\Category\Application\Converter\Checker\FieldsRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Infrastructure\Exception\ContentArrayConversionException;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use PHPUnit\Framework\TestCase;

class FieldsRequirementCheckerTest extends TestCase
{
    private FieldsRequirementChecker $sut;

    protected function setUp(): void
    {
        $this->sut = new FieldsRequirementChecker();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FieldsRequirementChecker::class, $this->sut);
        $this->assertInstanceOf(RequirementChecker::class, $this->sut);
    }

    public function test_it_does_not_raise_exception_when_all_required_fields_are_present(): void
    {
        $data = [
            'code' => 'socks',
            'labels' => [
                'en_US' => 'socks',
            ],
        ];
        $this->sut->check($data);
        $this->addToAssertionCount(1);
    }

    public function test_it_does_not_raise_exception_on_no_required_label(): void
    {
        $data = [
            'code' => 'socks',
            'labels' => null,
        ];
        $this->sut->check($data);
        $this->addToAssertionCount(1);
    }

    public function test_it_should_raise_exception_when_a_required_field_is_not_present(): void
    {
        $data = [
            'labels' => [
                'en_US' => 'socks',
            ],
        ];
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check($data);
    }

    public function test_it_does_not_raise_exception_when_a_code_field_cannot_be_empty(): void
    {
        $data = [
            'code' => 'socks',
            'labels' => null,
        ];
        $this->sut->check($data);
        $this->addToAssertionCount(1);
    }

    public function test_it_should_raise_exception_when_code_field_is_empty(): void
    {
        $data = [
            'code' => '',
            'labels' => null,
        ];
        $this->expectException(ContentArrayConversionException::class);
        $this->sut->check($data);
    }

    public function test_it_should_raise_exception_when_a_required_field_is_null(): void
    {
        $data = [
            'code' => null,
            'labels' => null,
        ];
        $this->expectException(ContentArrayConversionException::class);
        $this->sut->check($data);
    }

    public function test_it_does_not_raise_exception_when_a_parent_category_code_is_different_from_the_category_code(): void
    {
        $data = [
            'code' => 'socks',
            'labels' => null,
            'parent' => 'hat',
        ];
        $this->sut->check($data);
        $this->addToAssertionCount(1);
    }
}
