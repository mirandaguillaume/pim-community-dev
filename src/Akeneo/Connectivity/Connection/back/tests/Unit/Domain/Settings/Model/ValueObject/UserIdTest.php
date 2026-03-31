<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use PHPUnit\Framework\TestCase;

class UserIdTest extends TestCase
{
    private UserId $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_a_user_id(): void
    {
        $this->sut = new UserId(42);
        $this->assertTrue(\is_a(UserId::class, UserId::class, true));
    }

    public function test_it_provides_a_user_id(): void
    {
        $this->sut = new UserId(42);
        $this->assertSame(42, $this->sut->id());
    }

    public function test_it_validates_itself(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('User id must be positive.');
        new UserId(-2);
    }
}
