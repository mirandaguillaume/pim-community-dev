<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\WrongCredentialsConnection\Model\Write;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write\WrongCredentialsCombination;
use PHPUnit\Framework\TestCase;

class WrongCredentialsCombinationTest extends TestCase
{
    private WrongCredentialsCombination $sut;

    protected function setUp(): void
    {
        $this->sut = new WrongCredentialsCombination('connection', 'username');
    }

    public function test_it_is_a_wrong_credentials_combination(): void
    {
        $this->assertInstanceOf(WrongCredentialsCombination::class, $this->sut);
    }

    public function test_it_provides_a_connection_code(): void
    {
        $this->assertSame('connection', $this->sut->connectionCode());
    }

    public function test_it_provides_a_username(): void
    {
        $this->assertSame('username', $this->sut->username());
    }
}
