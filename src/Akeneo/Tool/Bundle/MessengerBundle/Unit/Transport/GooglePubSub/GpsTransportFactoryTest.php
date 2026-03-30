<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeySolver;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\GpsTransportFactory;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GpsTransportFactoryTest extends TestCase
{
    private PubSubClientFactory|MockObject $pubSubClientFactory;
    private OrderingKeySolver|MockObject $orderingKeySolver;
    private GpsTransportFactory $sut;

    protected function setUp(): void
    {
        $this->pubSubClientFactory = $this->createMock(PubSubClientFactory::class);
        $this->orderingKeySolver = $this->createMock(OrderingKeySolver::class);
        $this->sut = new GpsTransportFactory($this->pubSubClientFactory, $this->orderingKeySolver);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GpsTransportFactory::class, $this->sut);
    }

    public function test_it_supports_the_gps_dsn(): void
    {
        $this->assertSame(true, $this->sut->supports('gps:', []));
    }
}
