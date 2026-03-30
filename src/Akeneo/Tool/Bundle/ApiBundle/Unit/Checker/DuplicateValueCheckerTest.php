<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ApiBundle\Checker;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\ApiBundle\Checker\DuplicateValueChecker;

class DuplicateValueCheckerTest extends TestCase
{
    private DuplicateValueChecker $sut;

    protected function setUp(): void
    {
        $this->sut = new DuplicateValueChecker();
    }

    public function test_it_throws_exception_if_values_are_duplicated(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->check([
                    'values' => [
                        'a_simple_select' => [
                            ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                            ['locale' => null, 'scope' => null, 'data' => 'optionA'],
                        ],
                    ],
                ]);
    }

    public function test_it_does_not_throws_exception_if_values_are_different(): void
    {
        $this->sut->shouldNotThrow(InvalidPropertyTypeException::class)->during('check', [[
                    'values' => [
                        'a_simple_select' => [
                            ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                            ['locale' => null, 'scope' => 'ecommerce', 'data' => 'optionA'],
                        ],
                    ],
                ]]);
    }
}
