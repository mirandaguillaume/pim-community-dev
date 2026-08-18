<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductGridFilterController;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGridFilterControllerTest extends TestCase
{
    private Manager|MockObject $datagridManager;
    private TokenStorageInterface|MockObject $tokenStorage;
    private SearchableRepositoryInterface|MockObject $attributeSearchRepository;
    private NormalizerInterface|MockObject $lightAttributeNormalizer;
    private UserContext|MockObject $userContext;
    private ProductGridFilterController $sut;

    protected function setUp(): void
    {
        $this->datagridManager = $this->createMock(Manager::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->attributeSearchRepository = $this->createMock(SearchableRepositoryInterface::class);
        $this->lightAttributeNormalizer = $this->createMock(NormalizerInterface::class);
        $this->userContext = $this->createMock(UserContext::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(fn(string $id) => $id);

        $this->sut = new ProductGridFilterController(
            $this->datagridManager,
            $this->tokenStorage,
            $this->attributeSearchRepository,
            $this->lightAttributeNormalizer,
            $this->userContext,
            $translator,
        );

        $this->givenTheUser();
        $this->datagridManager->method('getConfigurationForGrid')->willReturn(DatagridConfiguration::create([
            'filters' => [
                'columns' => [
                    'family' => ['label' => 'label.family'],
                    'scope' => ['label' => 'label.scope'],
                    'locale' => ['label' => 'label.locale'],
                    'created_at' => ['label' => 'label.created_at'],
                ],
            ],
        ]));
    }

    public function test_it_merges_system_filters_and_attributes(): void
    {
        $this->attributeSearchRepository->method('findBySearch')->willReturn(['sku_attribute']);
        $this->lightAttributeNormalizer->method('normalize')->willReturn(['code' => 'sku']);

        $response = $this->sut->listAction(Request::create('/'));
        $data = \json_decode((string) $response->getContent(), true);

        $codes = \array_column($data, 'code');
        $this->assertContains('family', $codes);
        $this->assertContains('created_at', $codes);
        $this->assertContains('sku', $codes);
    }

    public function test_it_excludes_the_scope_and_locale_system_filters(): void
    {
        $this->attributeSearchRepository->method('findBySearch')->willReturn([]);

        $response = $this->sut->listAction(Request::create('/'));
        $data = \json_decode((string) $response->getContent(), true);

        $codes = \array_column($data, 'code');
        $this->assertNotContains('scope', $codes);
        $this->assertNotContains('locale', $codes);
    }

    public function test_it_filters_system_filters_by_the_search_term(): void
    {
        $this->attributeSearchRepository->method('findBySearch')->willReturn([]);

        $response = $this->sut->listAction(Request::create('/', 'GET', ['search' => 'family']));
        $data = \json_decode((string) $response->getContent(), true);

        $this->assertSame(['family'], \array_column($data, 'code'));
    }

    public function test_it_reduces_the_attribute_search_limit_by_the_number_of_matched_system_filters(): void
    {
        $this->attributeSearchRepository->expects($this->once())
            ->method('findBySearch')
            ->with(null, $this->callback(fn(array $options): bool => 20 - 2 === $options['limit']))
            ->willReturn([]);

        $this->sut->listAction(Request::create('/', 'GET', ['search' => '']));
    }

    public function test_it_parses_the_identifiers_parameter_into_a_unique_list(): void
    {
        $this->attributeSearchRepository->expects($this->once())
            ->method('findBySearch')
            ->with($this->anything(), $this->callback(fn(array $options): bool => ['sku-1', 'sku-2'] === $options['identifiers']))
            ->willReturn([]);

        $this->sut->listAction(Request::create('/', 'GET', ['identifiers' => 'sku-1,sku-2,sku-1']));
    }

    private function givenTheUser(): void
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getGroupsIds')->willReturn([1]);
        $locale = new Locale();
        $locale->setCode('en_US');
        $user->method('getUiLocale')->willReturn($locale);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->userContext->method('getUiLocaleCode')->willReturn('en_US');
    }
}
