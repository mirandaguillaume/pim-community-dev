<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\DeleteCustomAppCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\DeleteCustomAppHandler;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External\DeleteCustomAppAction;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCustomAppActionTest extends TestCase
{
    private SecurityFacade|MockObject $security;
    private DeleteCustomAppHandler|MockObject $deleteCustomAppHandler;
    private GetCustomAppQueryInterface|MockObject $getCustomAppQuery;
    private DeleteAppHandler|MockObject $deleteAppHandler;
    private DeleteCustomAppAction $sut;

    protected function setUp(): void
    {
        $this->security = $this->createMock(SecurityFacade::class);
        $this->deleteCustomAppHandler = $this->createMock(DeleteCustomAppHandler::class);
        $this->getCustomAppQuery = $this->createMock(GetCustomAppQueryInterface::class);
        $this->deleteAppHandler = $this->createMock(DeleteAppHandler::class);
        $this->sut = new DeleteCustomAppAction(
            $this->security,
            $this->deleteCustomAppHandler,
            $this->getCustomAppQuery,
            $this->deleteAppHandler,
        );
    }

    public function test_it_is_a_delete_custom_app_action(): void
    {
        $this->assertInstanceOf(DeleteCustomAppAction::class, $this->sut);
    }

    public function test_it_throws_an_access_denied_exception_when_connection_cannot_manage_custom_apps(): void
    {
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke('test_client_id');
    }

    public function test_it_throws_a_not_found_exception_when_client_id_do_not_belong_to_a_custom_app(): void
    {
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $this->getCustomAppQuery->method('execute')->with('test_client_id')->willReturn(null);
        $this->expectException(NotFoundHttpException::class);

        $this->expectExceptionMessage('Test app with test_client_id client_id was not found.');
        $this->sut->__invoke('test_client_id');
    }

    public function test_it_deletes_custom_app(): void
    {
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $this->getCustomAppQuery->method('execute')->with('test_client_id')->willReturn([
                    'some' => 'data',
                    'connected' => false,
                ]);
        $this->deleteCustomAppHandler->expects($this->once())->method('handle')->with($this->isInstanceOf(DeleteCustomAppCommand::class));
        $this->deleteAppHandler->expects($this->never())->method('handle')->with($this->isInstanceOf(DeleteAppCommand::class));
        $this->assertEquals(new JsonResponse(null, Response::HTTP_NO_CONTENT), $this->sut->__invoke('test_client_id'));
    }

    public function test_it_deletes_custom_app_and_underlying_connected_app(): void
    {
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $this->getCustomAppQuery->method('execute')->with('test_client_id')->willReturn([
                    'some' => 'data',
                    'connected' => true,
                ]);
        $this->deleteCustomAppHandler->expects($this->once())->method('handle')->with($this->isInstanceOf(DeleteCustomAppCommand::class));
        $this->deleteAppHandler->expects($this->once())->method('handle')->with($this->isInstanceOf(DeleteAppCommand::class));
        $this->assertEquals(new JsonResponse(null, Response::HTTP_NO_CONTENT), $this->sut->__invoke('test_client_id'));
    }
}
