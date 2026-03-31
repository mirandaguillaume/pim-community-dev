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
        $this->localeAware->method('getLocale')->willReturn('fr_FR');
        $version->method('isPending')->willReturn(false);
        $version->method('getAuthor')->willReturn('steve');
        $this->userManager->method('findUserByUsername')->willReturn($steve);
        $steve->method('getFirstName')->willReturn('Steve');
        $steve->method('getLastName')->willReturn('Jobs');
        $normalizedChangeset = [
                    'maximum_frame_rate' => ['old' => '', 'new' => '200,7890'],
                    'price-EUR'          => ['old' => "5,00 \u{20AC}", 'new' => "5,15 \u{20AC}"],
                    'weight'             => ['old' => '', 'new' => '10,1234'],
                    'asso-products'      => ['old' => '', 'new' => 'my-identifier'],
                ];
        $this->userContext->method('getUserTimezone')->willReturn('Europe/Paris');
        $this->attributeRepository->method('getAttributeTypeByCodes')->willReturn([
                        'maximum_frame_rate' => 'pim_catalog_number',
                        'price' => 'pim_catalog_price_collection',
                        'weight' => 'pim_catalog_metric',
                    ]);
        $this->presenterRegistry->method('getPresenterByAttributeType')->willReturnCallback(fn (string $type) => match ($type) {
            'pim_catalog_number' => $numberPresenter,
            'pim_catalog_price_collection' => $pricesPresenter,
            'pim_catalog_metric' => $metricPresenter,
            default => null,
        });
        $this->presenterRegistry->method('getPresenterByFieldCode')->willReturnCallback(fn (string $code) => match ($code) {
            'asso-products' => $productAssociationPresenter,
            default => null,
        });
        $numberPresenter->method('present')->willReturnCallback(fn (string $value) => match ($value) {
            '200.7890' => '200,7890',
            default => '',
        });
        $pricesPresenter->method('present')->willReturnCallback(fn (string $value) => match ($value) {
            '5.00' => "5,00 \u{20AC}",
            '5.15' => "5,15 \u{20AC}",
            default => '',
        });
        $metricPresenter->method('present')->willReturnCallback(fn (string $value) => match ($value) {
            '10.1234' => '10,1234',
            default => '',
        });
        $productAssociationPresenter->method('present')->willReturnCallback(fn (string $value) => match ($value) {
            $uuid => 'my-identifier',
            default => '',
        });
        $this->datetimePresenter->method('present')->willReturn('01/01/1985 09:41 AM');
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
        $this->datetimePresenter->method('present')->willReturn('01/01/1985 09:41 AM');
        $version->method('isPending')->willReturn(false);
        $version->method('getAuthor')->willReturn('steve');
        $this->userManager->method('findUserByUsername')->willReturn(null);
        $this->translator->method('trans')->willReturn('Utilisateur supprim' . "\u{00E9}");
        $this->userContext->method('getUserTimezone')->willThrowException(new \RuntimeException());
        $this->assertSame([
                    'id'          => 12,
                    'author'      => 'steve - Utilisateur supprim' . "\u{00E9}",
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
        $this->datetimePresenter->method('present')->willReturn('01/01/1985 09:41 AM');
        $version->method('isPending')->willReturn(false);
        $version->method('getAuthor')->willReturn('steve');
        $this->userManager->method('findUserByUsername')->willReturn($steve);
        $steve->method('getFirstName')->willReturn('Steve');
        $steve->method('getLastName')->willReturn('Jobs');
        $normalizedChangeset = [
                    '123' => ['old' => '', 'new' => '556'],
                ];
        $this->userContext->method('getUserTimezone')->willThrowException(new \RuntimeException());
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
