<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Audit\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCount;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ErrorCountTest extends TestCase
{
    private ErrorCount $sut;

    protected function setUp(): void
    {
        $this->sut = new ErrorCount(
            'erp',
            5
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ErrorCount::class, $this->sut);
    }

    public function test_it_normalizes_the_error_count(): void
    {
        $this->assertSame([
                    'connection_code' => 'erp',
                    'count' => 5,
                ], $this->sut->normalize());
    }
}
