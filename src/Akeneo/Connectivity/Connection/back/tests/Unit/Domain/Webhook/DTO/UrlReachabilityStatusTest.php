<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Webhook\DTO;

use Akeneo\Connectivity\Connection\Domain\Webhook\DTO\UrlReachabilityStatus;
use PHPUnit\Framework\TestCase;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlReachabilityStatusTest extends TestCase
{
    private UrlReachabilityStatus $sut;

    protected function setUp(): void
    {
        $this->sut = new UrlReachabilityStatus(true, 'Lorem ipsum dolor sit amet');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UrlReachabilityStatus::class, $this->sut);
    }

    public function test_it_returns_success(): void
    {
        $this->assertSame(true, $this->sut->success());
    }

    public function test_it_returns_message(): void
    {
        $this->assertSame('Lorem ipsum dolor sit amet', $this->sut->message());
    }

    public function test_it_normalizes(): void
    {
        $this->assertSame([
                        'success' => true,
                        'message' => 'Lorem ipsum dolor sit amet',
                    ], $this->sut->normalize());
    }
}
