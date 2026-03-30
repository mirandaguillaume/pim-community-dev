<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Service;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Service\RandomCodeGenerator;

class RandomCodeGeneratorTest extends TestCase
{
    private RandomCodeGenerator $sut;

    protected function setUp(): void
    {
        $this->sut = new RandomCodeGenerator();
    }

    public function test_it_generates_a_random_code(): void
    {
        $code = $this->generate();
        Assert::assertIsString($code);
        Assert::assertMatchesRegularExpression('|[a-zA-Z0-9]{60,120}|', $code);
    }
}
