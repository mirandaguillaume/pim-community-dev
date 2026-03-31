<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels\InMemoryChannels;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryChannelsTest extends TestCase
{
    private InMemoryChannels $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryChannels([
            'ecommerce' => 1,
            'mobile' => 2,
        ]);
    }

    public function test_it_gets_a_channel_id_from_its_code(): void
    {
        $this->assertSame(2, $this->sut->getIdByCode('mobile'));
    }

    public function test_it_gets_a_channel_code_from_its_id(): void
    {
        $this->assertSame('mobile', $this->sut->getCodeById(2));
    }

    public function test_it_returns_null_if_the_channel_does_not_exist(): void
    {
        $this->assertNull($this->sut->getIdByCode('print'));
        $this->assertNull($this->sut->getCodeById(42));
    }
}
