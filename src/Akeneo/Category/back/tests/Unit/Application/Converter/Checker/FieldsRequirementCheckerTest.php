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

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(FieldsRequirementChecker::class, $this->sut);
        $this->assertInstanceOf(RequirementChecker::class, $this->sut);
    }

    public function testItDoesNotRaiseExceptionWhenAllRequiredFieldsArePresent(): void
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

    public function testItDoesNotRaiseExceptionOnNoRequiredLabel(): void
    {
        $data = [
            'code' => 'socks',
            'labels' => null,
        ];
        $this->sut->check($data);
        $this->addToAssertionCount(1);
    }

    public function testItShouldRaiseExceptionWhenARequiredFieldIsNotPresent(): void
    {
        $data = [
            'labels' => [
                'en_US' => 'socks',
            ],
        ];
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check($data);
    }

    public function testItDoesNotRaiseExceptionWhenACodeFieldCannotBeEmpty(): void
    {
        $data = [
            'code' => 'socks',
            'labels' => null,
        ];
        $this->sut->check($data);
        $this->addToAssertionCount(1);
    }

    public function testItShouldRaiseExceptionWhenCodeFieldIsEmpty(): void
    {
        $data = [
            'code' => '',
            'labels' => null,
        ];
        $this->expectException(ContentArrayConversionException::class);
        $this->sut->check($data);
    }

    public function testItShouldRaiseExceptionWhenARequiredFieldIsNull(): void
    {
        $data = [
            'code' => null,
            'labels' => null,
        ];
        $this->expectException(ContentArrayConversionException::class);
        $this->sut->check($data);
    }

    public function testItDoesNotRaiseExceptionWhenAParentCategoryCodeIsDifferentFromTheCategoryCode(): void
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
