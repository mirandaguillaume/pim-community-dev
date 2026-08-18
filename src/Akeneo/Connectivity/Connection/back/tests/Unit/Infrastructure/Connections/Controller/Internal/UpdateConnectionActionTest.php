<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Connections\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\Controller\Internal\UpdateConnectionAction;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectionActionTest extends TestCase
{
    private UpdateConnectionHandler|MockObject $updateConnectionHandler;
    private SecurityFacade|MockObject $securityFacade;
    private UpdateConnectionAction $sut;

    protected function setUp(): void
    {
        $this->updateConnectionHandler = $this->createMock(UpdateConnectionHandler::class);
        $this->securityFacade = $this->createMock(SecurityFacade::class);
        $this->sut = new UpdateConnectionAction($this->updateConnectionHandler, $this->securityFacade);
    }

    public function test_it_throws_access_denied_when_the_permission_is_missing(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(false);

        $this->expectException(AccessDeniedException::class);

        ($this->sut)($this->requestFor('erp', ['label' => 'Erp']));
    }

    public function test_it_returns_a_bad_request_response_when_the_body_is_not_valid_json(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $request = Request::create('/', 'PUT', ['code' => 'erp'], [], [], [], '{not json');

        $response = ($this->sut)($request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(\json_encode(['message' => 'Invalid json message received']), $response->getContent());
    }

    public function test_it_updates_the_connection_and_returns_no_content(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->updateConnectionHandler->expects($this->once())
            ->method('handle')
            ->with(new UpdateConnectionCommand('erp', 'Erp', 'other', null, 'a_role', null, false));

        $response = ($this->sut)($this->requestFor('erp', [
            'label' => 'Erp',
            'flow_type' => 'other',
            'image' => null,
            'user_role_id' => 'a_role',
            'user_group_id' => null,
        ]));

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function test_it_returns_unprocessable_entity_when_the_handler_reports_constraint_violations(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $violations = new ConstraintViolationList([
            new ConstraintViolation('a message', '', [], '', 'label', ''),
        ]);
        $this->updateConnectionHandler->method('handle')
            ->willThrowException(new ConstraintViolationListException($violations));

        $response = ($this->sut)($this->requestFor('erp', ['label' => 'Erp']));

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame(
            \json_encode([
                'errors' => [['name' => 'label', 'reason' => 'a message']],
                'message' => ConstraintViolationListException::MESSAGE,
            ]),
            $response->getContent(),
        );
    }

    public function test_it_returns_a_bad_request_response_when_the_handler_throws_any_other_exception(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->updateConnectionHandler->method('handle')->willThrowException(new \RuntimeException('boom'));

        $response = ($this->sut)($this->requestFor('erp', ['label' => 'Erp']));

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(\json_encode(['message' => 'boom']), $response->getContent());
    }

    private function requestFor(string $code, array $body): Request
    {
        $body += ['flow_type' => 'other', 'image' => null, 'user_role_id' => 'a_role', 'user_group_id' => null];

        return Request::create('/', 'PUT', ['code' => $code], [], [], [], \json_encode($body));
    }
}
