<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Query\GetConnectionBusinessErrorsHandler;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read\BusinessError;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Controller\Internal\ErrorManagementAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ErrorManagementActionTest extends TestCase
{
    private GetConnectionBusinessErrorsHandler|MockObject $getConnectionBusinessErrorsHandler;
    private ErrorManagementAction $sut;

    protected function setUp(): void
    {
        $this->getConnectionBusinessErrorsHandler = $this->createMock(GetConnectionBusinessErrorsHandler::class);
        $this->sut = new ErrorManagementAction($this->getConnectionBusinessErrorsHandler);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ErrorManagementAction::class, $this->sut);
    }

    public function test_it_normalizes_business_errors(): void
    {
        $businessError1 = new BusinessError(
            'erp',
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            '{"message": "Error 1"}'
        );
        $this->assertSame([$businessError1->normalize()], $this->sut->normalizeBusinessErrors([$businessError1]));
    }
}
