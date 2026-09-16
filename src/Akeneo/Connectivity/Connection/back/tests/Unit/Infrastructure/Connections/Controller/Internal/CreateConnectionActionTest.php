<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Connections\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateClientInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\Controller\Internal\CreateConnectionAction;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * CreateConnectionHandler is final, so the action is exercised through a real handler
 * built on mocked collaborators.
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnectionActionTest extends TestCase
{
    private ValidatorInterface|MockObject $validator;
    private ConnectionRepositoryInterface|MockObject $repository;
    private CreateClientInterface|MockObject $createClient;
    private CreateUserInterface|MockObject $createUser;
    private FindAConnectionHandler|MockObject $findAConnectionHandler;
    private SecurityFacade|MockObject $securityFacade;
    private CreateConnectionAction $sut;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->repository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->createClient = $this->createMock(CreateClientInterface::class);
        $this->createUser = $this->createMock(CreateUserInterface::class);
        $this->findAConnectionHandler = $this->createMock(FindAConnectionHandler::class);
        $this->securityFacade = $this->createMock(SecurityFacade::class);

        $handler = new CreateConnectionHandler(
            $this->validator,
            $this->repository,
            $this->createClient,
            $this->createUser,
            $this->findAConnectionHandler,
        );
        $this->sut = new CreateConnectionAction($handler, $this->securityFacade);
    }

    public function test_it_throws_access_denied_when_the_permission_is_missing(): void
    {
        $this->securityFacade->method('isGranted')
            ->with('akeneo_connectivity_connection_manage_settings')
            ->willReturn(false);
        $this->validator->expects($this->never())->method('validate');
        $this->repository->expects($this->never())->method('create');

        $this->expectException(AccessDeniedException::class);

        ($this->sut)($this->requestWith(['code' => 'magento', 'label' => 'Magento', 'flow_type' => 'data_source']));
    }

    public function test_it_returns_a_bad_request_response_when_the_body_is_not_valid_json(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->repository->expects($this->never())->method('create');

        $response = ($this->sut)(Request::create('/rest/connections', 'POST', [], [], [], [], '{not json'));

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(['message' => 'Invalid json message received'], $this->decode($response));
    }

    public function test_it_creates_a_non_auditable_connection_and_returns_it_with_its_credentials(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with(new CreateConnectionCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false))
            ->willReturn(new ConstraintViolationList([]));
        $this->createUser->method('execute')->willReturn(new User(42, 'magento_1234', 'generated_password'));
        $this->createClient->method('execute')->willReturn(new Client(7, '7_client_id', 'client_secret'));
        $this->repository->expects($this->once())->method('create');
        $this->findAConnectionHandler->method('handle')
            ->with(new FindAConnectionQuery('magento'))
            ->willReturn(new ConnectionWithCredentials(
                'magento',
                'Magento Connector',
                FlowType::DATA_DESTINATION,
                null,
                '7_client_id',
                'client_secret',
                'magento_1234',
                '3',
                '4',
                false,
                'default',
            ));

        $response = ($this->sut)($this->requestWith([
            'code' => 'magento',
            'label' => 'Magento Connector',
            'flow_type' => FlowType::DATA_DESTINATION,
        ]));

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame([
            'code' => 'magento',
            'label' => 'Magento Connector',
            'flow_type' => FlowType::DATA_DESTINATION,
            'image' => null,
            'client_id' => '7_client_id',
            'secret' => 'client_secret',
            'username' => 'magento_1234',
            'password' => 'generated_password',
            'user_role_id' => '3',
            'user_group_id' => '4',
            'auditable' => false,
            'type' => 'default',
        ], $this->decode($response));
    }

    public function test_it_returns_unprocessable_entity_with_the_violations_and_creates_nothing(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList([
            new ConstraintViolation('akeneo_connectivity.connection.connection.constraint.code.required', '', [], '', 'code', ''),
            new ConstraintViolation('akeneo_connectivity.connection.connection.constraint.flow_type.invalid', '', [], '', 'flowType', ''),
        ]));
        $this->createUser->expects($this->never())->method('execute');
        $this->repository->expects($this->never())->method('create');

        $response = ($this->sut)($this->requestWith(['code' => '', 'label' => 'Magento', 'flow_type' => 'unknown']));

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame([
            'errors' => [
                ['name' => 'code', 'reason' => 'akeneo_connectivity.connection.connection.constraint.code.required'],
                ['name' => 'flowType', 'reason' => 'akeneo_connectivity.connection.connection.constraint.flow_type.invalid'],
            ],
            'message' => ConstraintViolationListException::MESSAGE,
        ], $this->decode($response));
    }

    public function test_it_returns_a_bad_request_response_when_the_creation_fails_for_another_reason(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList([]));
        $this->createUser->method('execute')->willThrowException(new \RuntimeException('The user could not be created.'));
        $this->repository->expects($this->never())->method('create');

        $response = ($this->sut)($this->requestWith(['code' => 'magento', 'label' => 'Magento', 'flow_type' => 'data_source']));

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(['message' => 'The user could not be created.'], $this->decode($response));
    }

    private function requestWith(array $body): Request
    {
        return Request::create('/rest/connections', 'POST', [], [], [], [], \json_encode($body));
    }

    private function decode(Response $response): array
    {
        return \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}
