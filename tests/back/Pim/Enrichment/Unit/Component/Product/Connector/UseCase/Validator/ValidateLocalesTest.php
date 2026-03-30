<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateLocales;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidateLocalesTest extends TestCase
{
    private ValidateLocales $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidateLocales();
    }

    public function test_it_validates_that_locales_exist_and_are_activated(): void
    {
        $localeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);

        $localeEn = new Locale();
        $localeEn->setCode('en_US');
        $localeEn->addChannel(new Channel());
        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $localeFr->addChannel(new Channel());
        $localeRepository->method('findOneByIdentifier')->with('en_US')->willReturn($localeEn);
        $localeRepository->method('findOneByIdentifier')->with('fr_FR')->willReturn($localeFr);
        $this->sut->validate(['en_US', 'fr_FR'], null);
    }

    public function test_it_throws_exception_when_locale_does_not_exist(): void
    {
        $localeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);

        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $localeFr->addChannel(new Channel());
        $localeRepository->method('findOneByIdentifier')->with('en_US')->willReturn(null);
        $localeRepository->method('findOneByIdentifier')->with('fr_FR')->willReturn($localeFr);
        $this->expectException(new InvalidQueryException('Locale "en_US" does not exist or is not activated.'));
        $this->sut->validate(['en_US', 'fr_FR'], null);
    }

    public function test_it_throws_exception_when_locale_is_not_activated(): void
    {
        $localeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);

        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $localeRepository->method('findOneByIdentifier')->with('fr_FR')->willReturn($localeFr);
        $this->expectException(new InvalidQueryException('Locale "fr_FR" does not exist or is not activated.'));
        $this->sut->validate(['fr_FR'], null);
    }

    public function test_it_throws_exception_when_locale_is_not_activated_for_the_provided_channel(): void
    {
        $localeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $channelRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);

        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $channel = new Channel();
        $channel->setCode('tablet');
        $channel->addLocale($localeFr);
        $channel = new Channel();
        $channel->setCode('ecommerce');
        $localeRepository->method('findOneByIdentifier')->with('fr_FR')->willReturn($localeFr);
        $channelRepository->method('findOneByIdentifier')->with('ecommerce')->willReturn($channel);
        $this->expectException(new InvalidQueryException('Locale "fr_FR" is not activated for the scope "ecommerce".'));
        $this->sut->validate(['fr_FR'], 'ecommerce');
    }

    public function test_it_validates_that_all_locales_are_activated_for_the_provided_channel(): void
    {
        $localeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $channelRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);

        $localeEn = new Locale();
        $localeEn->setCode('en_US');
        $localeEn->addChannel(new Channel());
        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $localeFr->addChannel(new Channel());
        $channel = new Channel();
        $channel->setCode('tablet');
        $channel->addLocale($localeFr);
        $channel->addLocale($localeEn);
        $localeRepository->method('findOneByIdentifier')->with('en_US')->willReturn($localeEn);
        $localeRepository->method('findOneByIdentifier')->with('fr_FR')->willReturn($localeFr);
        $channelRepository->method('findOneByIdentifier')->with('tablet')->willReturn($channel);
        $this->sut->validate(['en_US', 'fr_FR'], null);
    }
}
