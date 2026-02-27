<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final readonly class ListConnectionsAction
{
    public function __construct(
        private FetchConnectionsHandler $fetchConnectionsHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $search = \json_decode((string) $request->get('search', "[]"), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new JsonResponse(['message' => 'Invalid json message received'], Response::HTTP_BAD_REQUEST);
        }
        $query = new FetchConnectionsQuery($search);

        $connections = $this->fetchConnectionsHandler->handle($query);

        return new JsonResponse(
            \array_map(fn(Connection $connection): array => $connection->normalize(), $connections)
        );
    }
}
