<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read\BusinessError;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BusinessErrorTest extends TestCase
{
    private BusinessError $sut;

    protected function setUp(): void
    {
        $this->sut = new BusinessError(
            'erp',
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            '{"message": "Error 1"}'
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(BusinessError::class, $this->sut);
    }

    public function test_it_normalizes_the_business_error(): void
    {
        $this->assertSame([
                    'connection_code' => 'erp',
                    'date_time' => '2020-01-01T00:00:00+00:00',
                    'content' => ['message' => 'Error 1'],
                ], $this->sut->normalize());
    }
}
