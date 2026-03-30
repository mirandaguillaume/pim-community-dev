<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use PHPUnit\Framework\TestCase;

class ConnectionImageTest extends TestCase
{
    private ConnectionImage $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_a_connection_image(): void
    {
        $this->sut = new ConnectionImage('a/b/c/image_path.png');
        $this->assertTrue(\is_a(ConnectionImage::class, ConnectionImage::class, true));
    }

    public function test_it_provides_a_file_path(): void
    {
        $this->sut = new ConnectionImage('a/b/c/image_path.png');
        $this->assertSame('a/b/c/image_path.png', $this->sut->__toString());
    }

    public function test_it_throws_an_error_if_file_path_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('akeneo_connectivity.connection.connection.constraint.image.not_empty');
        new ConnectionImage('');
    }
}
