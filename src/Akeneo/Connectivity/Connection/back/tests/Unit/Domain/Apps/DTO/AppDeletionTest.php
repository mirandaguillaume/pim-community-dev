<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Apps\DTO;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppDeletion;
use PHPUnit\Framework\TestCase;

class AppDeletionTest extends TestCase
{
    private AppDeletion $sut;

    protected function setUp(): void
    {
        $this->sut = new AppDeletion(
            'app_id_123',
            'connection_code_123',
            'user_group_123',
            'ROLE_123',
        );
    }

    public function test_it_is_an_app_deletion(): void
    {
        $this->assertInstanceOf(AppDeletion::class, $this->sut);
    }

    public function test_it_provides_an_app_id(): void
    {
        $this->assertSame('app_id_123', $this->sut->getAppId());
    }

    public function test_it_provides_a_connection_code(): void
    {
        $this->assertSame('connection_code_123', $this->sut->getConnectionCode());
    }

    public function test_it_provides_an_user_group(): void
    {
        $this->assertSame('user_group_123', $this->sut->getUserGroupName());
    }

    public function test_it_provides_an_user_role(): void
    {
        $this->assertSame('ROLE_123', $this->sut->getUserRole());
    }
}
