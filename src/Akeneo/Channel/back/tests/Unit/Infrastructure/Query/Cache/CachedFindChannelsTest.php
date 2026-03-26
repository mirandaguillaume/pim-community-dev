<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\ConversionUnitCollection;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\LabelCollection;
use Akeneo\Channel\Infrastructure\Query\Cache\CachedFindChannels;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CachedFindChannelsTest extends TestCase
{
    private FindChannels|MockObject $findChannels;
    private CachedFindChannels $sut;

    protected function setUp(): void
    {
        $this->findChannels = $this->createMock(FindChannels::class);
        $this->sut = new CachedFindChannels($this->findChannels);
    }

    public function test_it_finds_all_channels_and_caches_them(): void
    {
        $this->findChannels->expects($this->once())->method('findAll')->willReturn([
                        new Channel(
                            'ecommerce',
                            ['en_US', 'fr_FR'],
                            LabelCollection::fromArray([
                                'en_US' => 'Ecommerce',
                            ]),
                            ['USD'],
                            ConversionUnitCollection::fromArray([
                                'an_measurement_attribute' => 'GRAM',
                                'another_measurement_attribute' => 'POUND',
                            ]),
                        ),
                        new Channel(
                            'mobile',
                            ['en_US'],
                            LabelCollection::fromArray([
                                'en_US' => 'Mobile',
                            ]),
                            ['EUR'],
                            ConversionUnitCollection::fromArray([
                                'an_measurement_attribute' => 'GRAM',
                                'another_measurement_attribute' => 'POUND',
                            ]),
                        ),
                    ]);
        $this->sut->findAll();
        $this->sut->findAll();
        $this->sut->findAll();
    }
}
