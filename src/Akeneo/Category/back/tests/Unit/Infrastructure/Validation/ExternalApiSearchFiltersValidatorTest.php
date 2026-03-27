<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Validation;

use Akeneo\Category\Infrastructure\Validation\ExternalApiSearchFiltersValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExternalApiSearchFiltersValidatorTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private ExternalApiSearchFiltersValidator $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->sut = new ExternalApiSearchFiltersValidator($this->validator);
    }

    public function test_it_validate_empty_array(): void
    {
        $searchFilters = [];
        $this->validator->expects($this->never())->method('validate');
        $this->sut->validate($searchFilters);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_code_filters(): void
    {
        $searchFilters = [
            "code" => [
                [
                    "operator" => "IN",
                    "value" => ["master"],
                ],
            ],
            "is_root" => [
                [
                    "operator" => "=",
                    "value" => true,
                ],
            ],
        ];
        $this->validator->expects($this->exactly(2))->method('validate')->willReturn(new ConstraintViolationList());
        $this->sut->validate($searchFilters);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_parent_filters(): void
    {
        $searchFilters = [
            "parent" => [
                [
                    "operator" => "=",
                    "value" => "master",
                ],
            ],
            "is_root" => [
                [
                    "operator" => "=",
                    "value" => true,
                ],
            ],
        ];
        $this->validator->expects($this->exactly(2))->method('validate')->willReturn(new ConstraintViolationList());
        $this->sut->validate($searchFilters);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_updated_filters(): void
    {
        $searchFilters = [
            "is_root" => [
                [
                    "operator" => "=",
                    "value" => true,
                ],
            ],
            "updated" => [
                [
                    "operator" => ">",
                    "value" => '2019-06-09T12:00:00+00:00',
                ],
            ],
        ];
        $this->validator->expects($this->exactly(2))->method('validate')->willReturn(new ConstraintViolationList());
        $this->sut->validate($searchFilters);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_single_code_filter(): void
    {
        $searchFilters = [
            "code" => [
                [
                    "operator" => "IN",
                    "value" => ["cat1", "cat2"],
                ],
            ],
        ];
        $this->validator->expects($this->once())->method('validate')->willReturn(new ConstraintViolationList());
        $this->sut->validate($searchFilters);
        $this->addToAssertionCount(1);
    }

    public function test_it_throws_exception_on_wrong_filter(): void
    {
        $searchFilters = [
            "test" => [
                [
                    "operator" => "IN",
                    "value" => ["master"],
                ],
            ],
        ];
        $this->validator->expects($this->never())->method('validate');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('unavailable filter "test"');
        $this->sut->validate($searchFilters);
    }

    public function test_it_throws_exception_with_available_filters_in_message(): void
    {
        $searchFilters = [
            "foobar" => [
                [
                    "operator" => "=",
                    "value" => "x",
                ],
            ],
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('code, parent, is_root, updated');
        $this->sut->validate($searchFilters);
    }

    public function test_it_throws_exception_on_validation_filter(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $searchFilters = [
            "code" => [
                [
                    "operator" => "IN",
                    "value" => ["master"],
                ],
            ],
        ];
        $violation->expects($this->once())->method('getMessage')->willReturn('error message');
        $violations = [
            $violation,
        ];
        $this->validator->expects($this->once())->method('validate')->willReturn(new ConstraintViolationList($violations));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('error message');
        $this->sut->validate($searchFilters);
    }

    public function test_it_concatenates_multiple_violation_messages(): void
    {
        $violation1 = $this->createMock(ConstraintViolationInterface::class);
        $violation2 = $this->createMock(ConstraintViolationInterface::class);

        $violation1->expects($this->once())->method('getMessage')->willReturn('first error');
        $violation2->expects($this->once())->method('getMessage')->willReturn('second error');

        $searchFilters = [
            "code" => [
                [
                    "operator" => "IN",
                    "value" => ["master"],
                ],
            ],
        ];
        $this->validator->expects($this->once())->method('validate')
            ->willReturn(new ConstraintViolationList([$violation1, $violation2]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('first error second error');
        $this->sut->validate($searchFilters);
    }

    public function test_it_validates_all_four_filter_types_together(): void
    {
        $searchFilters = [
            "code" => [["operator" => "IN", "value" => ["master"]]],
            "parent" => [["operator" => "=", "value" => "root"]],
            "is_root" => [["operator" => "=", "value" => false]],
            "updated" => [["operator" => ">", "value" => "2020-01-01T00:00:00+00:00"]],
        ];
        $this->validator->expects($this->exactly(4))->method('validate')->willReturn(new ConstraintViolationList());
        $this->sut->validate($searchFilters);
        $this->addToAssertionCount(1);
    }

    public function test_it_rejects_wrong_operator_for_code(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->expects($this->once())->method('getMessage')->willReturn('wrong operator');

        $searchFilters = [
            "code" => [
                [
                    "operator" => "=",
                    "value" => ["master"],
                ],
            ],
        ];
        $this->validator->expects($this->once())->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('wrong operator');
        $this->sut->validate($searchFilters);
    }

    public function test_it_rejects_non_array_value_for_code(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->expects($this->once())->method('getMessage')->willReturn('must send an array');

        $searchFilters = [
            "code" => [
                [
                    "operator" => "IN",
                    "value" => "not_an_array",
                ],
            ],
        ];
        $this->validator->expects($this->once())->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must send an array');
        $this->sut->validate($searchFilters);
    }

    public function test_it_rejects_wrong_operator_for_parent(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->expects($this->once())->method('getMessage')->willReturn('must use "=" operator');

        $searchFilters = [
            "parent" => [
                [
                    "operator" => "IN",
                    "value" => "master",
                ],
            ],
        ];
        $this->validator->expects($this->once())->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must use "=" operator');
        $this->sut->validate($searchFilters);
    }

    public function test_it_rejects_non_string_value_for_parent(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->expects($this->once())->method('getMessage')->willReturn('must send a parent code');

        $searchFilters = [
            "parent" => [
                [
                    "operator" => "=",
                    "value" => 123,
                ],
            ],
        ];
        $this->validator->expects($this->once())->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must send a parent code');
        $this->sut->validate($searchFilters);
    }

    public function test_it_rejects_wrong_operator_for_is_root(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->expects($this->once())->method('getMessage')->willReturn('must use "=" operator');

        $searchFilters = [
            "is_root" => [
                [
                    "operator" => "!=",
                    "value" => true,
                ],
            ],
        ];
        $this->validator->expects($this->once())->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate($searchFilters);
    }

    public function test_it_rejects_non_bool_value_for_is_root(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->expects($this->once())->method('getMessage')->willReturn('must send a bool');

        $searchFilters = [
            "is_root" => [
                [
                    "operator" => "=",
                    "value" => "true",
                ],
            ],
        ];
        $this->validator->expects($this->once())->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate($searchFilters);
    }

    public function test_it_rejects_wrong_operator_for_updated(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->expects($this->once())->method('getMessage')->willReturn('require the ">" operator');

        $searchFilters = [
            "updated" => [
                [
                    "operator" => "=",
                    "value" => "2020-01-01T00:00:00+00:00",
                ],
            ],
        ];
        $this->validator->expects($this->once())->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate($searchFilters);
    }

    public function test_it_rejects_invalid_datetime_for_updated(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->expects($this->once())->method('getMessage')->willReturn('not in a valid ISO 8601');

        $searchFilters = [
            "updated" => [
                [
                    "operator" => ">",
                    "value" => "not-a-date",
                ],
            ],
        ];
        $this->validator->expects($this->once())->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate($searchFilters);
    }
}
