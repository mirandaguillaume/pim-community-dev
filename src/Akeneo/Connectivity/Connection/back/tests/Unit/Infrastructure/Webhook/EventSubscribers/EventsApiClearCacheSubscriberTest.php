<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Webhook\EventSubscribers;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers\EventsApiClearCacheSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiClearCacheSubscriberTest extends TestCase
{
    private CacheClearerInterface|MockObject $cacheClearer;
    private EventsApiClearCacheSubscriber $sut;

    protected function setUp(): void
    {
        $this->cacheClearer = $this->createMock(CacheClearerInterface::class);
        $this->sut = new EventsApiClearCacheSubscriber($this->cacheClearer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EventsApiClearCacheSubscriber::class, $this->sut);
    }

    public function test_it_clears_the_cache(): void
    {
        $this->cacheClearer->expects($this->once())->method('clear');
        $this->sut->clearCache();
    }
}
