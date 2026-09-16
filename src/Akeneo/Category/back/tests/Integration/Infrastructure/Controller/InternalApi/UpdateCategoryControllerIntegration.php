<?php

declare(strict_types=1);

/**
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryBaseSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Test\IntegrationTestsBundle\Configuration\Catalog;
use Akeneo\Test\IntegrationTestsBundle\Doctrine\Connection\ConnectionCloser;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\Test\IntegrationTestsBundle\Loader\FixturesLoader;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Runs the category label edit of the removed Behat scenario edit_a_category.feature:11 through the internal API, with
 * the container-wired converter, ACL filters, command bus and savers behind UpdateCategoryController.
 *
 * Ported from the labels, attribute values and not-found tests of UpdateCategoryControllerEndToEnd, which only the
 * Category_EndToEnd_Test suite ran, and no CI job runs that suite.
 */
class UpdateCategoryControllerIntegration extends WebTestCase
{
    private const string EDIT_ACL = 'pim_enrich_product_category_edit';
    private const string TEMPLATE_UUID = '0ce0b1a1-8bcd-4a2c-8d80-ec3a1a29a0b2';
    private const string TEXT_ATTRIBUTE_UUID = '6b0c1c0a-2e5c-41c0-9bbf-4f2a80ba1f1e';
    private const string IMAGE_ATTRIBUTE_UUID = 'a1d0f7e6-4a0e-4de0-9f9f-4a7d0b24f3d9';
    private const string UNKNOWN_ATTRIBUTE_UUID = 'ffffffff-ffff-4fff-8fff-ffffffffffff';
    /** Same shape as the "photo" fixture of Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase, in the
     * order ImageDataValue::normalize() writes it. */
    private const array IMAGE_DATA = [
        'size' => 168107,
        'extension' => 'jpg',
        'file_path' => '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
        'mime_type' => 'image/jpeg',
        'original_filename' => 'shoes.jpg',
    ];

    private KernelBrowser $client;
    private int $categoryId;

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $this->service('akeneo_integration_tests.loader.fixtures_loader', FixturesLoader::class)
            ->load($this->service('akeneo_integration_tests.catalogs', Catalog::class)->useMinimalCatalog());
        $this->service('akeneo_integration_tests.security.system_user_authenticator', SystemUserAuthenticator::class)
            ->createSystemUser();
        $this->service('pim_connector.doctrine.cache_clearer', UnitOfWorkAndRepositoriesClearer::class)->clear();

        $this->categoryId = $this->createCategory('jeans', 'master');
    }

    protected function tearDown(): void
    {
        $this->service('akeneo_integration_tests.doctrine.connection.connection_closer', ConnectionCloser::class)
            ->closeConnections();

        parent::tearDown();
    }

    public function testItUpdatesTheCategoryLabels(): void
    {
        $this->assertSame([], $this->labelsOfTheCategory());
        $newLabels = ['de_DE' => 'Hose', 'en_US' => 'Pants', 'fr_FR' => 'Pantalon'];

        $this->logAs('julia');
        $response = $this->postLabels($newLabels);

        $this->assertStatusCode(Response::HTTP_OK, $response);
        $body = $this->decode($response);
        $this->assertTrue($this->valueAt($body, 'success'));
        $this->assertSame($this->categoryId, $this->valueAt($body, 'category', 'id'));
        $this->assertSame('jeans', $this->valueAt($body, 'category', 'properties', 'code'));
        $this->assertEquals($newLabels, $this->valueAt($body, 'category', 'properties', 'labels'));

        $this->assertEquals($newLabels, $this->labelsOfTheCategory());
        $this->assertSame('jeans', (string) $this->theCategory()->getCode());

        // The edit form reloads the category through the GET route.
        $response = $this->callApiRoute('pim_enriched_category_rest_get', ['id' => (string) $this->categoryId], Request::METHOD_GET);
        $this->assertStatusCode(Response::HTTP_OK, $response);
        $body = $this->decode($response);
        $this->assertSame('jeans', $this->valueAt($body, 'properties', 'code'));
        $this->assertEquals($newLabels, $this->valueAt($body, 'properties', 'labels'));
    }

    /**
     * The other half of the edit form: the attribute values of the category template of its tree. They go through
     * ValueUserIntentFactory, which turns each "code|uuid|channel|locale" entry into a SetText/SetImage user intent
     * after resolving the attribute type by uuid, and land in pim_catalog_category.value_collection through
     * UpsertCategoryBaseSql.
     *
     * The image value carries only the metadata the upload route produced; UpdateCategoryController never reads the
     * file itself, so no fixture file is needed.
     *
     * Fixtures mirror Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase::useTemplateFunctionalCatalog,
     * which the CI-run Category storage integration tests use: a template on the master tree, saved by
     * CategoryTemplateSaver + CategoryTreeTemplateSaver, and its attributes by CategoryTemplateAttributeSaver.
     */
    public function testItUpdatesTheCategoryAttributeValues(): void
    {
        $this->givenATemplateOnTheMasterTreeWithATextAndAnImageAttribute();
        $textKey = 'url_slug|' . self::TEXT_ATTRIBUTE_UUID;
        $imageKey = 'banner_image|' . self::IMAGE_ATTRIBUTE_UUID;
        $this->assertNull($this->theCategory()->getAttributes(), 'the category carries no value yet');

        $this->logAs('julia');
        $response = $this->postAttributes([
            // The text attribute is scopable and localizable, the image one is not: both key shapes are covered.
            "$textKey|ecommerce|en_US" => [
                'data' => 'winter-jeans',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => $textKey,
            ],
            $imageKey => [
                'data' => self::IMAGE_DATA,
                'channel' => null,
                'locale' => null,
                'attribute_code' => $imageKey,
            ],
            // ValueUserIntentFactory skips a value whose attribute uuid resolves to no attribute.
            'gone|' . self::UNKNOWN_ATTRIBUTE_UUID => [
                'data' => 'dropped',
                'channel' => null,
                'locale' => null,
                'attribute_code' => 'gone|' . self::UNKNOWN_ATTRIBUTE_UUID,
            ],
        ]);

        $this->assertStatusCode(Response::HTTP_OK, $response);
        $returned = $this->valueAt($this->decode($response), 'category', 'attributes');
        $this->assertIsArray($returned);
        $this->assertEqualsCanonicalizing(["$textKey|ecommerce|en_US", $imageKey], array_keys($returned));
        $this->assertNormalizedValue(
            ['data' => 'winter-jeans', 'type' => 'text', 'channel' => 'ecommerce', 'locale' => 'en_US', 'attribute_code' => $textKey],
            $returned["$textKey|ecommerce|en_US"],
        );
        $this->assertNormalizedValue(
            ['data' => self::IMAGE_DATA, 'type' => 'image', 'channel' => null, 'locale' => null, 'attribute_code' => $imageKey],
            $returned[$imageKey],
        );

        $attributes = $this->theCategory()->getAttributes();
        $this->assertNotNull($attributes);
        $text = $attributes->getValue('url_slug', self::TEXT_ATTRIBUTE_UUID, 'ecommerce', 'en_US');
        $this->assertInstanceOf(TextValue::class, $text);
        $this->assertSame('winter-jeans', $text->getValue());
        $image = $attributes->getValue('banner_image', self::IMAGE_ATTRIBUTE_UUID, null, null);
        $this->assertInstanceOf(ImageValue::class, $image);
        $this->assertSame(self::IMAGE_DATA, $image->getValue()?->normalize());

        // The edit form reloads the category through the GET route: it serves the same values.
        $response = $this->callApiRoute('pim_enriched_category_rest_get', ['id' => (string) $this->categoryId], Request::METHOD_GET);
        $this->assertStatusCode(Response::HTTP_OK, $response);
        $reloaded = $this->valueAt($this->decode($response), 'attributes');
        $this->assertIsArray($reloaded);
        $this->assertEqualsCanonicalizing(["$textKey|ecommerce|en_US", $imageKey], array_keys($reloaded));
        $this->assertNormalizedValue(
            ['data' => 'winter-jeans', 'type' => 'text', 'channel' => 'ecommerce', 'locale' => 'en_US', 'attribute_code' => $textKey],
            $reloaded["$textKey|ecommerce|en_US"],
        );
        $this->assertNormalizedValue(
            ['data' => self::IMAGE_DATA, 'type' => 'image', 'channel' => null, 'locale' => null, 'attribute_code' => $imageKey],
            $reloaded[$imageKey],
        );
    }

    public function testItForbidsTheUpdateWithoutTheCategoryEditPermission(): void
    {
        $this->revokeTheCategoryEditPermissionFromEveryRole();

        $this->logAs('julia');
        $response = $this->postLabels(['en_US' => 'Pants']);

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $response);
        $this->assertSame([], $this->labelsOfTheCategory());
    }

    public function testItReturnsNotFoundForAnUnknownCategory(): void
    {
        $this->logAs('julia');
        $response = $this->callApiRoute('pim_enriched_category_rest_update', ['id' => '999999999'], Request::METHOD_POST);

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $response);
    }

    /**
     * @param array<string, string> $labels
     */
    private function postLabels(array $labels): Response
    {
        $category = $this->theCategory();

        // The payload the category edit form sends.
        return $this->callApiRoute(
            'pim_enriched_category_rest_update',
            ['id' => (string) $this->categoryId],
            Request::METHOD_POST,
            json_encode([
                'id' => $this->categoryId,
                'parent' => $category->getParentId()?->getValue(),
                'root_id' => $category->getRootId()?->getValue(),
                'template_uuid' => null,
                'properties' => [
                    'code' => (string) $category->getCode(),
                    'labels' => $labels,
                ],
                'attributes' => [],
                'permissions' => [],
                'isRoot' => $category->isRoot(),
            ], JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function postAttributes(array $attributes): Response
    {
        $category = $this->theCategory();

        return $this->callApiRoute(
            'pim_enriched_category_rest_update',
            ['id' => (string) $this->categoryId],
            Request::METHOD_POST,
            json_encode([
                'id' => $this->categoryId,
                'parent' => $category->getParentId()?->getValue(),
                'root_id' => $category->getRootId()?->getValue(),
                'template_uuid' => self::TEMPLATE_UUID,
                'properties' => [
                    'code' => (string) $category->getCode(),
                    'labels' => [],
                ],
                'attributes' => $attributes,
                'permissions' => [],
                'isRoot' => $category->isRoot(),
            ], JSON_THROW_ON_ERROR),
        );
    }

    /**
     * Compares a normalized value without pinning the order of its keys.
     *
     * @param array<string, mixed> $expected
     */
    private function assertNormalizedValue(array $expected, mixed $actual): void
    {
        $this->assertIsArray($actual);
        $this->assertEqualsCanonicalizing(array_keys($expected), array_keys($actual), (string) json_encode($actual));
        foreach ($expected as $key => $value) {
            $this->assertSame($value, $actual[$key] ?? null, $key);
        }
    }

    private function givenATemplateOnTheMasterTreeWithATextAndAnImageAttribute(): void
    {
        $master = $this->service(GetCategoryInterface::class, GetCategoryInterface::class)->byCode('master');
        $masterId = $master instanceof Category ? $master->getId() : null;
        if (null === $masterId) {
            throw new \LogicException('The category tree "master" does not exist.');
        }

        $templateUuid = TemplateUuid::fromString(self::TEMPLATE_UUID);
        $template = new Template(
            $templateUuid,
            new TemplateCode('jeans_template'),
            LabelCollection::fromArray(['en_US' => 'Jeans template']),
            $masterId,
            AttributeCollection::fromArray([
                AttributeText::create(
                    AttributeUuid::fromString(self::TEXT_ATTRIBUTE_UUID),
                    new AttributeCode('url_slug'),
                    AttributeOrder::fromInteger(1),
                    AttributeIsRequired::fromBoolean(false),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'URL slug']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([]),
                ),
                AttributeImage::create(
                    AttributeUuid::fromString(self::IMAGE_ATTRIBUTE_UUID),
                    new AttributeCode('banner_image'),
                    AttributeOrder::fromInteger(2),
                    AttributeIsRequired::fromBoolean(false),
                    AttributeIsScopable::fromBoolean(false),
                    AttributeIsLocalizable::fromBoolean(false),
                    LabelCollection::fromArray(['en_US' => 'Banner image']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([]),
                ),
            ]),
        );

        $this->service(CategoryTemplateSaver::class, CategoryTemplateSaver::class)->insert($template);
        $this->service(CategoryTreeTemplateSaver::class, CategoryTreeTemplateSaver::class)->insert($template);
        $this->service(CategoryTemplateAttributeSaver::class, CategoryTemplateAttributeSaver::class)
            ->insert($templateUuid, $template->getAttributeCollection());
    }

    private function createCategory(string $code, string $parentCode): int
    {
        $parent = $this->service(GetCategoryInterface::class, GetCategoryInterface::class)->byCode($parentCode);
        if (!$parent instanceof Category) {
            throw new \LogicException(sprintf('The category "%s" does not exist.', $parentCode));
        }

        $this->service(CategoryBaseSaver::class, CategoryBaseSaver::class)->save(new Category(
            id: null,
            code: new Code($code),
            templateUuid: null,
            parentId: $parent->getId(),
            parentCode: new Code($parentCode),
            rootId: $parent->getId(),
        ));

        $categoryId = $this->service(GetCategoryInterface::class, GetCategoryInterface::class)->byCode($code)?->getId();
        if (null === $categoryId) {
            throw new \LogicException(sprintf('The category "%s" was not created.', $code));
        }

        return $categoryId->getValue();
    }

    private function theCategory(): Category
    {
        $category = $this->service(GetCategoryInterface::class, GetCategoryInterface::class)->byId($this->categoryId);
        if (!$category instanceof Category) {
            throw new \LogicException('The category of the test does not exist.');
        }

        return $category;
    }

    /**
     * @return array<string, string>
     */
    private function labelsOfTheCategory(): array
    {
        return $this->theCategory()->getLabels()?->getTranslations() ?? [];
    }

    /**
     * Same approach as tests/back/UserManagement/Integration/Bundle/DuplicateUserIntegration.php. The logged-in user
     * gets every role, so the permission is revoked from all of them.
     */
    private function revokeTheCategoryEditPermissionFromEveryRole(): void
    {
        $aclManager = $this->service('oro_security.acl.manager', AclManager::class);
        $roles = $this->service('pim_user.repository.role', ObjectRepository::class)->findAll();
        $this->assertNotEmpty($roles);

        foreach ($roles as $role) {
            if (!$role instanceof RoleInterface) {
                continue;
            }
            $sid = $aclManager->getSid($role);
            foreach ($aclManager->getAllExtensions() as $extension) {
                foreach ($extension->getClasses() as $aclClassInfo) {
                    if (self::EDIT_ACL === $aclClassInfo->getClassName()) {
                        $oid = new ObjectIdentity($extension->getExtensionKey(), $aclClassInfo->getClassName());
                        $aclManager->setPermission($sid, $oid, AccessLevel::NONE_LEVEL, true);
                    }
                }
            }
        }

        $aclManager->flush();
    }

    private function logAs(string $username): void
    {
        $this->service('akeneo_integration_tests.helper.authenticator', AuthenticatorHelper::class)
            ->logIn($username, $this->client);
    }

    /**
     * @param array<string, string> $routeArguments
     */
    private function callApiRoute(string $route, array $routeArguments, string $method, ?string $content = null): Response
    {
        $this->client->request(
            $method,
            $this->service('router', RouterInterface::class)->generate($route, $routeArguments),
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
        $decoded = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($decoded)) {
            throw new \LogicException(sprintf('The response is not a JSON object: %s', $response->getContent()));
        }

        return $decoded;
    }

    /**
     * @param array<mixed> $data
     */
    private function valueAt(array $data, string ...$path): mixed
    {
        $value = $data;
        foreach ($path as $key) {
            if (!\is_array($value) || !\array_key_exists($key, $value)) {
                $this->fail(sprintf('No "%s" in the response: %s', implode('.', $path), json_encode($data)));
            }
            $value = $value[$key];
        }

        return $value;
    }

    private function assertStatusCode(int $expected, Response $response): void
    {
        $this->assertSame(
            $expected,
            $response->getStatusCode(),
            sprintf('Unexpected status code, content: %s', $response->getContent()),
        );
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function service(string $id, string $class): object
    {
        $service = self::getContainer()->get($id);
        if (!$service instanceof $class) {
            throw new \LogicException(sprintf('The service "%s" is not a %s.', $id, $class));
        }

        return $service;
    }
}
