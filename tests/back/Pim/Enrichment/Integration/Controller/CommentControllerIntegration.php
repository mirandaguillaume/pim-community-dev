<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller;

use Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Doctrine\Common\Util\ClassUtils;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard of the product comments panel: a comment is posted to pim_enrich_product_comments_rest_post, listed by
 * pim_enrich_product_comments_rest_get and deleted through pim_comment_comment_delete (InternalApi\CommentController).
 * It replaces the PR-time protection of Behat comments.feature:58, whose Playwright spec
 * tests/front/e2e/product/comments.spec.ts does not run on backend-only changes.
 *
 * CommentControllerTest covers the delete branches with mocks. This test runs the real wiring instead: the route, the
 * service arguments, the Doctrine remover, and the author check that compares the Doctrine-loaded author with the user
 * of the security token. The delete is sent with the DELETE verb the route declares.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentControllerIntegration extends WebTestCase
{
    protected KernelBrowser $client;

    public function test_a_user_can_delete_their_own_comment(): void
    {
        $uuid = $this->createProduct('commented_product');
        $this->logIn('julia');

        $response = $this->callApiRoute(
            Request::METHOD_POST,
            'pim_enrich_product_comments_rest_post',
            ['uuid' => $uuid],
            \json_encode(['body' => 'My own comment'], JSON_THROW_ON_ERROR)
        );

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $comment = \json_decode((string) $response->getContent(), true);
        Assert::assertIsInt($comment['id']);
        Assert::assertSame('julia', $comment['author']['username']);
        Assert::assertCount(1, $this->listCommentsFromInternalApi($uuid));

        // Start the delete from an empty identity map, as a real request does.
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $response = $this->callApiRoute(
            Request::METHOD_DELETE,
            'pim_comment_comment_delete',
            ['id' => (string) $comment['id']]
        );

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        Assert::assertSame([], $this->listCommentsFromInternalApi($uuid));
        Assert::assertSame([], $this->getStoredComments($uuid));
    }

    public function test_a_user_cannot_delete_a_comment_written_by_someone_else(): void
    {
        $uuid = $this->createProduct('commented_product');
        $commentId = $this->createCommentAuthoredBy('mary', $uuid, 'Comment written by Mary');
        $this->logIn('julia');

        $response = $this->callApiRoute(
            Request::METHOD_DELETE,
            'pim_comment_comment_delete',
            ['id' => (string) $commentId]
        );

        Assert::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), (string) $response->getContent());
        $storedComments = $this->getStoredComments($uuid);
        Assert::assertCount(1, $storedComments);
        Assert::assertSame('mary', $storedComments[0]->getAuthor()->getUserIdentifier());

        $listedComments = $this->listCommentsFromInternalApi($uuid);
        Assert::assertCount(1, $listedComments);
        Assert::assertSame($commentId, $listedComments[0]['id']);
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($this->get('akeneo_integration_tests.catalogs')->useTechnicalCatalog());

        $this->get('akeneo_integration_tests.security.system_user_authenticator')->createSystemUser();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function get(string $service)
    {
        return self::getContainer()->get($service);
    }

    private function createProduct(string $identifier): string
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: []
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->get('pim_catalog.validator.unique_value_set')->reset();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        Assert::assertNotNull($product, \sprintf('Product "%s" was not created', $identifier));

        return $product->getUuid()->toString();
    }

    private function createCommentAuthoredBy(string $username, string $productUuid, string $body): int
    {
        $author = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertNotNull($author, \sprintf('No user exists with username "%s"', $username));
        $product = $this->get('pim_catalog.repository.product')->find($productUuid);

        $comment = $this->get('pim_comment.builder.comment')->buildComment($product, $author);
        $comment->setBody($body);
        $this->get('pim_comment.saver.comment')->save($comment);
        $commentId = (int) $comment->getId();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        return $commentId;
    }

    private function logIn(string $username): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $this->client);
    }

    private function callApiRoute(string $method, string $route, array $routeArguments, ?string $content = null): Response
    {
        $this->client->request(
            $method,
            $this->get('router')->generate($route, $routeArguments),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            $content
        );

        return $this->client->getResponse();
    }

    private function listCommentsFromInternalApi(string $uuid): array
    {
        $response = $this->callApiRoute(Request::METHOD_GET, 'pim_enrich_product_comments_rest_get', ['uuid' => $uuid]);
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        return \json_decode((string) $response->getContent(), true);
    }

    /**
     * @return CommentInterface[] read back from the database
     */
    private function getStoredComments(string $uuid): array
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $product = $this->get('pim_catalog.repository.product')->find($uuid);
        Assert::assertNotNull($product, \sprintf('Product %s not found', $uuid));

        return $this->get('pim_comment.repository.comment')->getCommentsByUuid(
            ClassUtils::getClass($product),
            $product->getUuid()
        );
    }

    private function getUserId(string $username): int
    {
        $id = $this->get('database_connection')->executeQuery(
            'SELECT id FROM oro_user WHERE username = :username',
            ['username' => $username]
        )->fetchOne();
        if (false === $id || null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }
}
