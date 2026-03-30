<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\EventSubscriber;

use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Tool\Component\Api\Event\ApiAuthenticationEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\EventSubscriber\ConnectionContextEventSubscriber;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionContextEventSubscriberTest extends TestCase
{
    private ConnectionContext|MockObject $connectionContext;
    private ConnectionContextEventSubscriber $sut;

    protected function setUp(): void
    {
        $this->connectionContext = $this->createMock(ConnectionContext::class);
        $this->sut = new ConnectionContextEventSubscriber($this->connectionContext);
    }

    public function test_it_initializes_connection_context(): void
    {
        $event = new ApiAuthenticationEvent('magento_0123', '42');
        $this->connectionContext->expects($this->once())->method('setClientId')->with('42');
        $this->connectionContext->expects($this->once())->method('setUsername')->with('magento_0123');
        $this->sut->initializeConnectionContext($event);
    }
}
