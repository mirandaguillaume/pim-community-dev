<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\Channel;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Currency;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Test\Acceptance\Channel\InMemoryFindActivatedCurrencies;
use PHPUnit\Framework\TestCase;

class InMemoryFindActivatedCurrenciesTest extends TestCase
{
    private InMemoryFindActivatedCurrencies $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryFindActivatedCurrencies();
    }

}
