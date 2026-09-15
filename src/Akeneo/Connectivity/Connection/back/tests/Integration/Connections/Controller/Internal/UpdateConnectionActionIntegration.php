<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Connections\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Guards the "edit connection settings" flow end to end over HTTP: route, ACL, the container's
 * validation config, UpdateConnectionHandler and the database, then the two endpoints the UI
 * reloads afterwards. Ported from UpdateConnectionEndToEnd, whose suite never runs in CI.
 * Backend guard for the deleted Behat scenario edit_connection.feature:7.
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateConnectionActionIntegration extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Each test sends several requests: keep a single kernel so they share the session and the container.
        $this->client->disableReboot();
    }

    public function test_it_updates_the_connection_label_and_the_list_and_detail_endpoints_return_it(): void
    {
        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE, false);
        $this->authenticateAsAdmin();

        $this->postUpdate('magento', $this->payloadFor($connection, ['label' => 'NEWLABEL']));

        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());
        Assert::assertSame('', $response->getContent());

        $this->client->request('GET', $this->generateUrl('akeneo_connectivity_connection_rest_list'));
        Assert::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $listedConnection = $this->findListedConnection('magento');
        Assert::assertSame('NEWLABEL', $listedConnection['label']);
        Assert::assertSame(FlowType::DATA_SOURCE, $listedConnection['flowType']);

        $this->client->request('GET', $this->generateUrl('akeneo_connectivity_connection_rest_get', ['code' => 'magento']));
        Assert::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $fetchedConnection = $this->decodeResponse();
        Assert::assertSame('NEWLABEL', $fetchedConnection['label']);
        Assert::assertSame(FlowType::DATA_SOURCE, $fetchedConnection['flow_type']);
        Assert::assertSame($connection->userRoleId(), $fetchedConnection['user_role_id']);
    }

    public function test_it_rejects_an_empty_label_and_keeps_the_previous_one(): void
    {
        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE, false);
        $this->authenticateAsAdmin();

        $this->postUpdate('magento', $this->payloadFor($connection, ['label' => '']));

        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        Assert::assertSame(
            [
                'errors' => [
                    [
                        'name' => 'label',
                        'reason' => 'akeneo_connectivity.connection.connection.constraint.label.required',
                    ],
                ],
                'message' => 'akeneo_connectivity.connection.constraint_violation_list_exception',
            ],
            $this->decodeResponse()
        );
        Assert::assertSame('Magento', $this->fetchStoredLabel('magento'));
    }

    public function test_it_returns_a_bad_request_for_an_unknown_connection(): void
    {
        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE, false);
        $this->authenticateAsAdmin();

        $this->postUpdate('unknown_connection', $this->payloadFor($connection, ['label' => 'NEWLABEL']));

        Assert::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        Assert::assertSame(
            ['message' => 'Connection with code "unknown_connection" does not exist'],
            $this->decodeResponse()
        );
        Assert::assertSame('Magento', $this->fetchStoredLabel('magento'));
    }

    public function test_it_is_forbidden_without_the_manage_settings_permission(): void
    {
        $connection = $this->createConnection('magento', 'Magento', FlowType::DATA_SOURCE, false);
        $this->authenticateAsAdmin();
        $this->removeAclFromRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_settings');

        $this->postUpdate('magento', $this->payloadFor($connection, ['label' => 'NEWLABEL']));

        Assert::assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
        Assert::assertSame('Magento', $this->fetchStoredLabel('magento'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * Same body as the one the connection settings form sends.
     *
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function payloadFor(ConnectionWithCredentials $connection, array $overrides): array
    {
        return \array_merge([
            'code' => $connection->code(),
            'label' => $connection->label(),
            'flow_type' => $connection->flowType(),
            'image' => null,
            'user_role_id' => $connection->userRoleId(),
            'user_group_id' => $connection->userGroupId(),
            'auditable' => false,
        ], $overrides);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function postUpdate(string $code, array $payload): void
    {
        $this->client->request(
            'POST',
            $this->generateUrl('akeneo_connectivity_connection_rest_update', ['code' => $code]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            \json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param array<string, string> $parameters
     */
    private function generateUrl(string $route, array $parameters = []): string
    {
        $router = $this->get('router');
        \assert($router instanceof RouterInterface);

        return $router->generate($route, $parameters);
    }

    /**
     * @return array<mixed>
     */
    private function decodeResponse(): array
    {
        $decoded = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        \assert(\is_array($decoded));

        return $decoded;
    }

    /**
     * @return array<mixed>
     */
    private function findListedConnection(string $code): array
    {
        foreach ($this->decodeResponse() as $listedConnection) {
            if (\is_array($listedConnection) && $code === ($listedConnection['code'] ?? null)) {
                return $listedConnection;
            }
        }

        Assert::fail(\sprintf('The connection "%s" is not in the connection list.', $code));
    }

    private function fetchStoredLabel(string $code): string
    {
        $dbalConnection = $this->get('database_connection');
        \assert($dbalConnection instanceof DbalConnection);

        $label = $dbalConnection->fetchOne(
            'SELECT label FROM akeneo_connectivity_connection WHERE code = :code',
            ['code' => $code]
        );
        \assert(\is_string($label));

        return $label;
    }
}
