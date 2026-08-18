<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Connections\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\Controller\Internal\GetConnectionAction;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectionActionTest extends TestCase
{
    private FindAConnectionHandler|MockObject $findAConnectionHandler;
    private SecurityFacade|MockObject $securityFacade;
    private GetConnectionAction $sut;

    protected function setUp(): void
    {
        $this->findAConnectionHandler = $this->createMock(FindAConnectionHandler::class);
        $this->securityFacade = $this->createMock(SecurityFacade::class);
        $this->sut = new GetConnectionAction($this->findAConnectionHandler, $this->securityFacade);
    }

    public function test_it_throws_access_denied_when_the_permission_is_missing(): void
    {
        $this->securityFacade->method('isGranted')
            ->with('akeneo_connectivity_connection_manage_settings')
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);

        ($this->sut)(Request::create('/', 'GET', ['code' => 'erp']));
    }

    public function test_it_returns_not_found_when_no_connection_matches_the_code(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->findAConnectionHandler->method('handle')
            ->with(new FindAConnectionQuery('erp'))
            ->willReturn(null);

        $response = ($this->sut)(Request::create('/', 'GET', ['code' => 'erp']));

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_it_returns_not_found_when_the_connection_is_not_of_type_default(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->findAConnectionHandler->method('handle')
            ->willReturn($this->connectionOfType('other'));

        $response = ($this->sut)(Request::create('/', 'GET', ['code' => 'erp']));

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_it_returns_the_normalized_connection_when_it_is_of_type_default(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $connection = $this->connectionOfType('default');
        $this->findAConnectionHandler->method('handle')->willReturn($connection);

        $response = ($this->sut)(Request::create('/', 'GET', ['code' => 'erp']));

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(\json_encode($connection->normalize()), $response->getContent());
    }

    private function connectionOfType(string $type): ConnectionWithCredentials
    {
        return new ConnectionWithCredentials(
            'erp',
            'Erp',
            'other',
            null,
            'a_client_id',
            'a_secret',
            'a_username',
            'a_user_role_id',
            null,
            false,
            $type,
        );
    }
}
