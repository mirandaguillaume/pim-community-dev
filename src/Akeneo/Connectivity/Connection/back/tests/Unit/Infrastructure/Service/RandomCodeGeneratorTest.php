<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Service;

use Akeneo\Connectivity\Connection\Infrastructure\Service\RandomCodeGenerator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class RandomCodeGeneratorTest extends TestCase
{
    private RandomCodeGenerator $sut;

    protected function setUp(): void
    {
        $this->sut = new RandomCodeGenerator();
    }

    public function test_it_generates_a_random_code(): void
    {
        $code = $this->sut->generate();
        Assert::assertIsString($code);
        Assert::assertMatchesRegularExpression('|[a-zA-Z0-9]{60,120}|', $code);
    }
}
