<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Standard;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeTranslation;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TranslationNormalizerTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $localeRepository;
    private TranslationNormalizer $sut;

    protected function setUp(): void
    {
        $this->localeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->sut = new TranslationNormalizer($this->localeRepository);
    }

    public function test_it_normalize_even_if_the_locale_case_is_wrong(): void
    {
        $translatable = $this->createMock(TranslatableInterface::class);
        $english = $this->createMock(AttributeTranslation::class);
        $french = $this->createMock(AttributeTranslation::class);

        $localeEn = $this->createMock(LocaleInterface::class);
        $localeEn->method('isActivated')->willReturn(true);
        $localeFr = $this->createMock(LocaleInterface::class);
        $localeFr->method('isActivated')->willReturn(true);
        $this->localeRepository->method('findOneByIdentifier')->willReturnMap([
            ['en_US', $localeEn],
            ['FR_FR', $localeFr],
        ]);

        $translatable->method('getTranslations')->willReturn([
                    $english, $french
                ]);
        $english->method('getLocale')->willReturn('en_US');
        $english->method('getLabel')->willReturn('foo');
        $french->method('getLocale')->willReturn('FR_FR');
        $french->method('getLabel')->willReturn('bar');
        $this->assertSame([
                        'en_US' => 'foo',
                        'fr_FR' => 'bar',
                    ], $this->sut->normalize($translatable, 'standard', [
                    'property' => 'label',
                    'locales' => ['en_US', 'fr_FR']
                ]));
    }

    public function test_it_normalize_even_if_the_context_locale_case_is_wrong(): void
    {
        $translatable = $this->createMock(TranslatableInterface::class);
        $english = $this->createMock(AttributeTranslation::class);
        $french = $this->createMock(AttributeTranslation::class);

        $localeEn = $this->createMock(LocaleInterface::class);
        $localeEn->method('isActivated')->willReturn(true);
        $localeFr = $this->createMock(LocaleInterface::class);
        $localeFr->method('isActivated')->willReturn(true);
        $this->localeRepository->method('findOneByIdentifier')->willReturnMap([
            ['en_US', $localeEn],
            ['fr_FR', $localeFr],
        ]);

        $translatable->method('getTranslations')->willReturn([
                    $english, $french
                ]);
        $english->method('getLocale')->willReturn('en_US');
        $english->method('getLabel')->willReturn('foo');
        $french->method('getLocale')->willReturn('fr_FR');
        $french->method('getLabel')->willReturn('bar');
        $this->assertSame([
                        'en_US' => 'foo',
                        'FR_FR' => null,
                        'fr_FR' => 'bar',
                    ], $this->sut->normalize($translatable, 'standard', [
                    'property' => 'label',
                    'locales' => ['en_US', 'FR_FR']
                ]));
    }
}
