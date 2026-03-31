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
    private IdentifiableObjectRepositoryInterface|MockObject $channelRepository;
    private IdentifiableObjectRepositoryInterface|MockObject $localeRepository;
    private ValidateLocales $sut;

    protected function setUp(): void
    {
        $this->channelRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->localeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->sut = new ValidateLocales($this->channelRepository, $this->localeRepository);
    }

    public function test_it_validates_that_locales_exist_and_are_activated(): void
    {
        $localeEn = new Locale();
        $localeEn->setCode('en_US');
        $localeEn->addChannel(new Channel());
        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $localeFr->addChannel(new Channel());
        $this->localeRepository->method('findOneByIdentifier')->willReturnMap([
            ['en_US', $localeEn],
            ['fr_FR', $localeFr],
        ]);
        $this->sut->validate(['en_US', 'fr_FR'], null);
        $this->addToAssertionCount(1);
    }

    public function test_it_throws_exception_when_locale_does_not_exist(): void
    {
        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $localeFr->addChannel(new Channel());
        $this->localeRepository->method('findOneByIdentifier')->willReturnMap([
            ['en_US', null],
            ['fr_FR', $localeFr],
        ]);
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Locale "en_US" does not exist or is not activated.');
        $this->sut->validate(['en_US', 'fr_FR'], null);
    }

    public function test_it_throws_exception_when_locale_is_not_activated(): void
    {
        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $this->localeRepository->method('findOneByIdentifier')->willReturnMap([
            ['fr_FR', $localeFr],
        ]);
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Locale "fr_FR" does not exist or is not activated.');
        $this->sut->validate(['fr_FR'], null);
    }

    public function test_it_throws_exception_when_locale_is_not_activated_for_the_provided_channel(): void
    {
        $localeFr = new Locale();
        $localeFr->setCode('fr_FR');
        $localeFr->addChannel(new Channel());
        $channel = new Channel();
        $channel->setCode('ecommerce');
        $this->localeRepository->method('findOneByIdentifier')->willReturnMap([
            ['fr_FR', $localeFr],
        ]);
        $this->channelRepository->method('findOneByIdentifier')->willReturnMap([
            ['ecommerce', $channel],
        ]);
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Locale "fr_FR" is not activated for the scope "ecommerce".');
        $this->sut->validate(['fr_FR'], 'ecommerce');
    }

    public function test_it_validates_that_all_locales_are_activated_for_the_provided_channel(): void
    {
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
        $this->localeRepository->method('findOneByIdentifier')->willReturnMap([
            ['en_US', $localeEn],
            ['fr_FR', $localeFr],
        ]);
        $this->channelRepository->method('findOneByIdentifier')->willReturnMap([
            ['tablet', $channel],
        ]);
        $this->sut->validate(['en_US', 'fr_FR'], null);
        $this->addToAssertionCount(1);
    }
}
