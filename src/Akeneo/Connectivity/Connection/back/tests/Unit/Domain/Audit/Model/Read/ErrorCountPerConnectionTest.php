<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Audit\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCountPerConnection;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ErrorCountPerConnectionTest extends TestCase
{
    private ErrorCountPerConnection $sut;

    protected function setUp(): void
    {
        $errorCount1 = new ErrorCount('erp', 5);
        $errorCount2 = new ErrorCount('ecommerce', 8);
        $this->sut = new ErrorCountPerConnection([$errorCount1, $errorCount2]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ErrorCountPerConnection::class, $this->sut);
    }

    public function test_it_normalizes_the_error_count_per_connection(): void
    {
        $this->assertSame([
                    'erp' => 5,
                    'ecommerce' => 8,
                ], $this->sut->normalize());
    }

    public function test_it_normalizes_when_zero_error_count_per_connection(): void
    {
        $this->sut = new ErrorCountPerConnection([]);
        $this->assertSame([], $this->sut->normalize());
    }
}
