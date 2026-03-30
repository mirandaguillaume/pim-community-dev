<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Pagination;

use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginationParametersValidator;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
use PHPUnit\Framework\TestCase;

class PaginationParametersValidatorTest extends TestCase
{
    private PaginationParametersValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new PaginationParametersValidator(['pagination' => ['limit_max' => 100]]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(PaginationParametersValidator::class, $this->sut);
    }

    public function test_it_is_a_parameter_validator(): void
    {
        $this->assertInstanceOf(ParameterValidatorInterface::class, $this->sut);
    }

    public function test_it_validates_offset_pagination_by_default(): void
    {
        $parameters = [
                    'page'  => '1.1',
                ];
        $this->sut->shouldThrow(new PaginationParametersException('"1.1" is not a valid page number.'))
                    ->duringValidate($parameters);
    }

    public function test_it_validates_limit_with_search_after_pagination(): void
    {
        $parameters = [
                    'limit'  => '1.1',
                ];
        $this->sut->shouldThrow(new PaginationParametersException('"1.1" is not a valid limit number.'))
                    ->duringValidate($parameters);
    }

    public function test_it_validates_with_count_with_boolean_string_values(): void
    {
        $this->sut->validate(['with_count' => 'true']);
        $this->sut->validate(['with_count' => 'false']);
    }

    public function test_it_ignores_with_count_with_search_after_pagination(): void
    {
        $this->sut->validate(['with_count' => '1', 'pagination_type' => 'search_after'], ['support_search_after' => true]);
        $this->sut->validate(['with_count' => '0', 'pagination_type' => 'search_after'], ['support_search_after' => true]);
    }

    public function test_it_validates_integer_values_with_offset_pagination(): void
    {
        $parameters = [
                    'page'            => 1,
                    'limit'           => 10,
                    'pagination_type' => 'page',
                ];
        $this->sut->validate($parameters);
    }

    public function test_it_validates_integer_as_string_values_with_offset_pagination(): void
    {
        $parameters = [
                    'page'            => '1',
                    'limit'           => '10',
                    'pagination_type' => 'page',
                ];
        $this->sut->validate($parameters);
    }

    public function test_it_does_not_validates_float_page_values_with_offset_pagination(): void
    {
        $parameters = [
                    'page'            => '1.1',
                    'pagination_type' => 'page',
                ];
        $this->sut->shouldThrow(new PaginationParametersException('"1.1" is not a valid page number.'))
                    ->duringValidate($parameters);
    }

    public function test_it_does_not_validate_string_page_value_with_offset_pagination(): void
    {
        $parameters = [
                    'page'            => 'string',
                    'pagination_type' => 'page',
                ];
        $this->sut->shouldThrow(new PaginationParametersException('"string" is not a valid page number.'))
                    ->duringValidate($parameters);
    }

    public function test_it_does_not_validate_negative_page_number_with_offset_pagination(): void
    {
        $parameters = [
                    'page'            => -5,
                    'pagination_type' => 'page',
                ];
        $this->sut->shouldThrow(new PaginationParametersException('"-5" is not a valid page number.'))
                    ->duringValidate($parameters);
    }

    public function test_it_does_not_validates_float_limit_values_with_offset_pagination(): void
    {
        $parameters = [
                    'limit'           => '1.1',
                    'pagination_type' => 'page',
                ];
        $this->sut->shouldThrow(new PaginationParametersException('"1.1" is not a valid limit number.'))
                    ->duringValidate($parameters);
    }

    public function test_it_does_not_validate_string_limit_value_with_offset_pagination(): void
    {
        $parameters = [
                    'limit'           => 'string',
                    'pagination_type' => 'page',
                ];
        $this->sut->shouldThrow(new PaginationParametersException('"string" is not a valid limit number.'))
                    ->duringValidate($parameters);
    }

    public function test_it_does_not_validate_negative_limit_number_with_offset_pagination(): void
    {
        $parameters = [
                    'limit'           => -5,
                    'pagination_type' => 'page',
                ];
        $this->sut->shouldThrow(new PaginationParametersException('"-5" is not a valid limit number.'))
                    ->duringValidate($parameters);
    }

    public function test_it_does_not_validate_limit_exceeding_maximum_limit_value_with_offset_pagination(): void
    {
        $parameters = [
                    'limit'           => 200,
                    'pagination_type' => 'page',
                ];
        $this->sut->shouldThrow(new PaginationParametersException('You cannot request more than 100 items.'))
                    ->duringValidate($parameters);
    }

    public function test_it_throws_an_exception_when_unknown_pagination_type(): void
    {
        $this->sut->shouldThrow(new PaginationParametersException('Pagination type does not exist.'))
                    ->duringValidate(['pagination_type' => 'unknown']);
    }

    public function test_it_throws_an_exception_when_search_after_pagination_not_supported(): void
    {
        $this->sut->shouldThrow(new PaginationParametersException('Pagination type does not exist.'))
                    ->duringValidate(['pagination_type' => 'unknown']);
        $this->sut->shouldThrow(new PaginationParametersException('Pagination type does not exist.'))
                    ->duringValidate(['pagination_type' => 'unknown'], ['support_search_after' => false]);
    }

    public function test_it_throws_an_exception_when_parameter_with_count_is_not_a_boolean(): void
    {
        $this->sut->shouldThrow(
            new PaginationParametersException(
                'Parameter "with_count" has to be a boolean. Only "true" or "false" allowed, "1" given.'
            )
        )
                    ->duringValidate(['with_count' => '1']);
        $this->sut->shouldThrow(
            new PaginationParametersException(
                'Parameter "with_count" has to be a boolean. Only "true" or "false" allowed, "0" given.'
            )
        )
                    ->duringValidate(['with_count' => '0']);
    }
}
