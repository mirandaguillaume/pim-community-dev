<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\GenerateSecretInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\GenerateSecret;
use PHPUnit\Framework\TestCase;

class GenerateSecretTest extends TestCase
{
    private GenerateSecret $sut;

    protected function setUp(): void
    {
        $this->sut = new GenerateSecret();
    }

    public function test_it_is_a_generate_secret_service(): void
    {
        $this->assertInstanceOf(GenerateSecret::class, $this->sut);
        $this->assertInstanceOf(GenerateSecretInterface::class, $this->sut);
    }

    public function test_it_generates_a_secret(): void
    {
        $secret = $this->generate();
        $secret->shouldBeString();
    }
}
