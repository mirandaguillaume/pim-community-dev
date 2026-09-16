<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Connections\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\Controller\Internal\ListConnectionsAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListConnectionsActionTest extends TestCase
{
    private FetchConnectionsHandler|MockObject $fetchConnectionsHandler;
    private ListConnectionsAction $sut;

    protected function setUp(): void
    {
        $this->fetchConnectionsHandler = $this->createMock(FetchConnectionsHandler::class);
        $this->sut = new ListConnectionsAction($this->fetchConnectionsHandler);
    }

    public function test_it_returns_a_bad_request_response_when_the_search_parameter_is_not_valid_json(): void
    {
        $response = ($this->sut)(Request::create('/', 'GET', ['search' => '{not json']));

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame(
            \json_encode(['message' => 'Invalid json message received']),
            $response->getContent()
        );
    }

    public function test_it_defaults_to_an_empty_search_when_no_search_parameter_is_given(): void
    {
        $this->fetchConnectionsHandler->expects($this->once())
            ->method('handle')
            ->with(new FetchConnectionsQuery([]))
            ->willReturn([]);

        $response = ($this->sut)(Request::create('/'));

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('[]', $response->getContent());
    }

    public function test_it_returns_the_normalized_connections_matching_the_search(): void
    {
        $connection = new Connection('erp', 'Erp', 'other', null, false, 'default');
        $this->fetchConnectionsHandler->method('handle')
            ->with(new FetchConnectionsQuery(['types' => ['default']]))
            ->willReturn([$connection]);

        $response = ($this->sut)(Request::create('/', 'GET', ['search' => \json_encode(['types' => ['default']])]));

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(\json_encode([$connection->normalize()]), $response->getContent());
    }
}
