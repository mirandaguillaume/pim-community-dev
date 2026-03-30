<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\VersionNormalizer;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class VersionNormalizerTest extends TestCase
{
    private UserManager|MockObject $userManager;
    private TranslatorInterface|MockObject $translator;
    private LocaleAwareInterface|MockObject $localeAware;
    private PresenterInterface|MockObject $datetimePresenter;
    private PresenterRegistryInterface|MockObject $presenterRegistry;
    private AttributeRepositoryInterface|MockObject $attributeRepository;
    private UserContext|MockObject $userContext;
    private VersionNormalizer $sut;

    protected function setUp(): void
    {
        $this->userManager = $this->createMock(UserManager::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->localeAware = $this->createMock(LocaleAwareInterface::class);
        $this->datetimePresenter = $this->createMock(PresenterInterface::class);
        $this->presenterRegistry = $this->createMock(PresenterRegistryInterface::class);
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->sut = new VersionNormalizer($this->userManager,
            $this->translator,
            $this->localeAware,
            $this->datetimePresenter,
            $this->presenterRegistry,
            $this->attributeRepository,
            $this->userContext);
    }

    public function test_it_supports_versions(): void
    {
        $version = $this->createMock(Version::class);

        $this->assertSame(true, $this->sut->supportsNormalization($version, 'internal_api'));
    }

    public function test_it_normalize_versions(): void
    {
        $version = $this->createMock(Version::class);
        $steve = $this->createMock(User::class);
        $numberPresenter = $this->createMock(PresenterInterface::class);
        $pricesPresenter = $this->createMock(PresenterInterface::class);
        $metricPresenter = $this->createMock(PresenterInterface::class);
        $productAssociationPresenter = $this->createMock(PresenterInterface::class);

        $versionTime = new \DateTime();
        $uuid = Uuid::uuid4()->toString();
        $changeset = [
                    'maximum_frame_rate' => ['old' => '', 'new' => '200.7890'],
                    'price-EUR'          => ['old' => '5.00', 'new' => '5.15'],
                    'weight'             => ['old' => '', 'new' => '10.1234'],
                    'asso-products'      => ['old' => '', 'new' => $uuid],
                ];
        $version->method('getId')->willReturn(12);
        $version->method('getResourceId')->willReturn(112);
        $version->method('getSnapshot')->willReturn('a nice snapshot');
        $version->method('getChangeset')->willReturn($changeset);
        $version->method('getContext')->willReturn(['locale' => 'en_US', 'channel' => 'mobile']);
        $version->method('getVersion')->willReturn(12);
        $version->method('getLoggedAt')->willReturn($versionTime);
        $this->localeAware->method('getLocale')->willReturn('en_US');
        $version->method('isPending')->willReturn(false);
        $version->method('getAuthor')->willReturn('steve');
        $this->userManager->method('findUserByUsername')->with('steve')->willReturn($steve);
        $steve->method('getFirstName')->willReturn('Steve');
        $steve->method('getLastName')->willReturn('Jobs');
        $normalizedChangeset = [
                    'maximum_frame_rate' => ['old' => '', 'new' => '200,7890'],
                    'price-EUR'          => ['old' => '5,00 €', 'new' => '5,15 €'],
                    'weight'             => ['old' => '', 'new' => '10,1234'],
                    'asso-products'      => ['old' => '', 'new' => 'my-identifier'],
                ];
        $options = [
                    'locale' => 'fr_FR',
                ];
        $datetimePresenterOtions = [
                    'locale' => 'fr_FR',
                    'timezone' => 'Europe/Paris',
                ];
        $this->localeAware->method('getLocale')->willReturn('fr_FR');
        $this->userContext->method('getUserTimezone')->willReturn('Europe/Paris');
        $this->attributeRepository->method('getAttributeTypeByCodes')->with(['maximum_frame_rate', 'price', 'weight', 'asso'])->willReturn([
                        'maximum_frame_rate' => 'pim_catalog_number',
                        'price' => 'pim_catalog_price_collection',
                        'weight' => 'pim_catalog_metric',
                    ]);
        $this->presenterRegistry->method('getPresenterByAttributeType')->with('pim_catalog_number')->willReturn($numberPresenter);
        $this->presenterRegistry->method('getPresenterByAttributeType')->with('pim_catalog_price_collection')->willReturn($pricesPresenter);
        $this->presenterRegistry->method('getPresenterByAttributeType')->with('pim_catalog_metric')->willReturn($metricPresenter);
        $this->presenterRegistry->method('getPresenterByFieldCode')->with('asso-products')->willReturn($productAssociationPresenter);
        $numberPresenter->method('present')->with('200.7890', $options + ['versioned_attribute' => 'maximum_frame_rate', 'attribute' => 'maximum_frame_rate'])->willReturn('200,7890');
        $pricesPresenter->method('present')->with('5.00', $options + ['versioned_attribute' => 'price-EUR', 'attribute' => 'price'])->willReturn('5,00 €');
        $pricesPresenter->method('present')->with('5.15', $options + ['versioned_attribute' => 'price-EUR', 'attribute' => 'price'])->willReturn('5,15 €');
        $metricPresenter->method('present')->with('10.1234', $options + ['versioned_attribute' => 'weight', 'attribute' => 'weight'])->willReturn('10,1234');
        $productAssociationPresenter->method('present')->with($uuid, $options + ['versioned_attribute' => 'asso-products', 'attribute' => 'asso'])->willReturn('my-identifier');
        $numberPresenter->method('present')->with('', $options + ['versioned_attribute' => 'maximum_frame_rate', 'attribute' => 'maximum_frame_rate'])->willReturn('');
        $pricesPresenter->method('present')->with('', $options)->willReturn('');
        $metricPresenter->method('present')->with('', $options + ['versioned_attribute' => 'weight', 'attribute' => 'weight'])->willReturn('');
        $this->datetimePresenter->method('present')->with($versionTime, $datetimePresenterOtions)->willReturn('01/01/1985 09:41 AM');
        $productAssociationPresenter->method('present')->with('', $options + ['versioned_attribute' => 'asso-products', 'attribute' => 'asso'])->willReturn('');
        $this->assertSame([
                    'id'          => 12,
                    'author'      => 'Steve Jobs',
                    'resource_id' => '112',
                    'snapshot'    => 'a nice snapshot',
                    'changeset'   => $normalizedChangeset,
                    'context'     => ['locale' => 'en_US', 'channel' => 'mobile'],
                    'version'     => 12,
                    'logged_at'   => '01/01/1985 09:41 AM',
                    'pending'     => false,
                ], $this->sut->normalize($version, 'internal_api'));
    }

    public function test_it_normalize_versions_with_deleted_user(): void
    {
        $version = $this->createMock(Version::class);

        $versionTime = new \DateTime();
        $version->method('getId')->willReturn(12);
        $version->method('getResourceId')->willReturn(112);
        $version->method('getSnapshot')->willReturn('a nice snapshot');
        $version->method('getChangeset')->willReturn(['text' => 'the changeset']);
        $version->method('getContext')->willReturn(['locale' => 'en_US', 'channel' => 'mobile']);
        $version->method('getVersion')->willReturn(12);
        $version->method('getLoggedAt')->willReturn($versionTime);
        $this->localeAware->method('getLocale')->willReturn('en_US');
        $this->datetimePresenter->method('present')->with($versionTime, $this->anything())->willReturn('01/01/1985 09:41 AM');
        $version->method('isPending')->willReturn(false);
        $version->method('getAuthor')->willReturn('steve');
        $this->userManager->method('findUserByUsername')->with('steve')->willReturn(null);
        $this->translator->method('trans')->with('pim_user.user.removed_user')->willReturn('Utilisateur supprimé');
        $this->userContext->method('getUserTimezone')->willThrowException(\RuntimeException::class);
        $this->assertSame([
                    'id'          => 12,
                    'author'      => 'steve - Utilisateur supprimé',
                    'resource_id' => '112',
                    'snapshot'    => 'a nice snapshot',
                    'changeset'   => ['text' => 'the changeset'],
                    'context'     => ['locale' => 'en_US', 'channel' => 'mobile'],
                    'version'     => 12,
                    'logged_at'   => '01/01/1985 09:41 AM',
                    'pending'     => false,
                ], $this->sut->normalize($version, 'internal_api'));
    }

    public function test_it_normalize_versions_with_numeric_code_as_attribute(): void
    {
        $version = $this->createMock(Version::class);
        $steve = $this->createMock(User::class);

        $versionTime = new \DateTime();
        $changeset = [
                    123 => ['old' => '', 'new' => '556'],
                ];
        $version->method('getId')->willReturn(12);
        $version->method('getResourceId')->willReturn(112);
        $version->method('getSnapshot')->willReturn('a nice snapshot');
        $version->method('getChangeset')->willReturn($changeset);
        $version->method('getContext')->willReturn(['locale' => 'en_US', 'channel' => 'mobile']);
        $version->method('getVersion')->willReturn(12);
        $version->method('getLoggedAt')->willReturn($versionTime);
        $this->localeAware->method('getLocale')->willReturn('en_US');
        $this->datetimePresenter->method('present')->with($versionTime, $this->anything())->willReturn('01/01/1985 09:41 AM');
        $version->method('isPending')->willReturn(false);
        $version->method('getAuthor')->willReturn('steve');
        $this->userManager->method('findUserByUsername')->with('steve')->willReturn($steve);
        $steve->method('getFirstName')->willReturn('Steve');
        $steve->method('getLastName')->willReturn('Jobs');
        $normalizedChangeset = [
                    '123' => ['old' => '', 'new' => '556'],
                ];
        $this->userContext->method('getUserTimezone')->willThrowException(\RuntimeException::class);
        $this->assertSame([
                    'id'          => 12,
                    'author'      => 'Steve Jobs',
                    'resource_id' => '112',
                    'snapshot'    => 'a nice snapshot',
                    'changeset'   => $normalizedChangeset,
                    'context'     => ['locale' => 'en_US', 'channel' => 'mobile'],
                    'version'     => 12,
                    'logged_at'   => '01/01/1985 09:41 AM',
                    'pending'     => false,
                ], $this->sut->normalize($version, 'internal_api'));
    }
}
