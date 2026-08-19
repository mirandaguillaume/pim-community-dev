<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Model;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleTest extends TestCase
{
    private Locale $sut;

    protected function setUp(): void
    {
        $this->sut = new Locale();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Locale::class, $this->sut);
    }

    public function test_it_returns_itself_when_setting_the_code(): void
    {
        $this->assertSame($this->sut, $this->sut->setCode('en_US'));
        $this->assertSame('en_US', $this->sut->getCode());
    }

    public function test_it_has_no_channel_until_one_is_added(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $this->assertFalse($this->sut->hasChannel($channel));
    }

    public function test_it_is_not_activated_until_a_channel_is_added(): void
    {
        $this->assertFalse($this->sut->isActivated());
    }

    public function test_it_adds_a_channel_and_becomes_activated(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $result = $this->sut->addChannel($channel);

        $this->assertSame($this->sut, $result);
        $this->assertTrue($this->sut->hasChannel($channel));
        $this->assertTrue($this->sut->isActivated());
    }

    public function test_it_does_not_add_the_same_channel_twice(): void
    {
        $channel = $this->createMock(ChannelInterface::class);

        $this->sut->addChannel($channel);
        $this->sut->addChannel($channel);

        $this->assertCount(1, $this->sut->getChannels());
    }

    public function test_it_removes_a_channel_and_deactivates_when_it_was_the_last_one(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $this->sut->addChannel($channel);

        $result = $this->sut->removeChannel($channel);

        $this->assertSame($this->sut, $result);
        $this->assertFalse($this->sut->hasChannel($channel));
        $this->assertFalse($this->sut->isActivated());
    }

    public function test_it_stays_activated_when_removing_a_channel_while_others_remain(): void
    {
        $channelToRemove = $this->createMock(ChannelInterface::class);
        $anotherChannel = $this->createMock(ChannelInterface::class);
        $this->sut->addChannel($channelToRemove);
        $this->sut->addChannel($anotherChannel);

        $this->sut->removeChannel($channelToRemove);

        $this->assertTrue($this->sut->isActivated());
    }
}
