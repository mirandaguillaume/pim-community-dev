<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ApiBundle\Security;

use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2Storage;
use Akeneo\Tool\Component\Api\Event\ApiAuthenticationFailedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\ApiBundle\Security\OAuth2;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OAuth2Test extends TestCase
{
    private IOAuth2Storage|MockObject $storage;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private OAuth2 $sut;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(IOAuth2Storage::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->sut = new OAuth2($this->storage, $this->eventDispatcher);
    }

    public function test_it_dispatches_an_event_when_a_verified_token_is_not_valid(): void
    {
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->isInstanceOf(ApiAuthenticationFailedEvent::class));
        $this->expectException(new HttpException(401, 'The access token provided is invalid.'));
        $this->sut->verifyAccessToken('TpwH4anEPRPwkJN7rLV5T8oMyQN95');
    }
}
