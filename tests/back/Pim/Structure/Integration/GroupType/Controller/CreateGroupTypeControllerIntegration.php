<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\GroupType\Controller;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard for the group type creation modal, previously covered by the Behat scenario
 * create_group_type.feature:13: POST /configuration/rest/group-type (form_extensions/group_type/create.yml) runs
 * GroupTypeController::createAction (factory, updater, validator, internal_api violation normalizer, saver and
 * internal_api GroupTypeNormalizer), then the redirect to the edit page loads the group type back.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateGroupTypeControllerIntegration extends WebTestCase
{
    private KernelBrowser $client;

    public function test_it_creates_a_group_type_and_loads_it_back(): void
    {
        $response = $this->createGroupType(['code' => 'special']);

        $this->assertStatusCode(Response::HTTP_OK, $response);
        $created = $this->decode($response);
        Assert::assertSame('special', $created['code'] ?? null);
        Assert::assertSame('group_type', $created['meta']['model_type'] ?? null);
        Assert::assertIsInt($created['meta']['id'] ?? null);

        $this->get('doctrine.orm.entity_manager')->clear();
        $groupType = $this->get('pim_catalog.repository.group_type')->findOneByIdentifier('special');
        Assert::assertNotNull($groupType, 'The group type should be saved.');
        Assert::assertSame($created['meta']['id'], $groupType->getId());

        // The group type edit page the modal redirects to fetches it through pim_enrich_grouptype_rest_get.
        $response = $this->callApiRoute('pim_enrich_grouptype_rest_get', ['identifier' => 'special'], Request::METHOD_GET);
        $this->assertStatusCode(Response::HTTP_OK, $response);
        $fetched = $this->decode($response);
        Assert::assertSame('special', $fetched['code'] ?? null);
        Assert::assertSame($created['meta']['id'], $fetched['meta']['id'] ?? null);
    }

    public function test_it_returns_the_violation_of_a_code_already_used(): void
    {
        // RELATED is a group type of the minimal catalog.
        $response = $this->createGroupType(['code' => 'RELATED']);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $response);
        Assert::assertSame(
            ['values' => [['path' => 'code', 'message' => 'This value is already used.', 'global' => false]]],
            $this->decode($response),
        );
    }

    public function test_it_returns_the_violation_of_a_code_with_forbidden_characters(): void
    {
        $response = $this->createGroupType(['code' => 'spe-cial']);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $response);
        Assert::assertSame(
            ['values' => [[
                'path' => 'code',
                'message' => 'Group type code may contain only letters, numbers and underscores.',
                'global' => false,
            ]]],
            $this->decode($response),
        );
        $this->get('doctrine.orm.entity_manager')->clear();
        Assert::assertNull($this->get('pim_catalog.repository.group_type')->findOneByIdentifier('spe-cial'));
    }

    public function test_it_redirects_a_request_that_is_not_an_xml_http_request(): void
    {
        $this->logIn();
        $this->client->request(
            Request::METHOD_POST,
            $this->get('router')->generate('pim_enrich_grouptype_rest_create'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['code' => 'special'], JSON_THROW_ON_ERROR),
        );

        Assert::assertTrue($this->client->getResponse()->isRedirect('/'));
        $this->get('doctrine.orm.entity_manager')->clear();
        Assert::assertNull($this->get('pim_catalog.repository.group_type')->findOneByIdentifier('special'));
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $this->get('akeneo_integration_tests.loader.fixtures_loader')
            ->load($this->get('akeneo_integration_tests.catalogs')->useMinimalCatalog());
        $this->get('akeneo_integration_tests.security.system_user_authenticator')->createSystemUser();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function tearDown(): void
    {
        $this->get('akeneo_integration_tests.doctrine.connection.connection_closer')->closeConnections();

        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $groupType
     */
    private function createGroupType(array $groupType): Response
    {
        $this->logIn();

        return $this->callApiRoute(
            'pim_enrich_grouptype_rest_create',
            [],
            Request::METHOD_POST,
            json_encode($groupType, JSON_THROW_ON_ERROR),
        );
    }

    private function logIn(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin', $this->client);
    }

    private function callApiRoute(string $route, array $routeArguments, string $method, ?string $content = null): Response
    {
        $this->client->request(
            $method,
            $this->get('router')->generate($route, $routeArguments),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            $content,
        );

        return $this->client->getResponse();
    }

    /**
     * @return array<mixed>
     */
    private function decode(Response $response): array
    {
        return json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function assertStatusCode(int $expected, Response $response): void
    {
        Assert::assertSame(
            $expected,
            $response->getStatusCode(),
            sprintf('Unexpected status code, content: %s', $response->getContent()),
        );
    }

    private function get(string $service): mixed
    {
        return self::getContainer()->get($service);
    }
}
