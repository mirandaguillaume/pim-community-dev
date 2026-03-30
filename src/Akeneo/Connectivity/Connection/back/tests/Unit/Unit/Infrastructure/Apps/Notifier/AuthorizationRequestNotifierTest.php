<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Notifier;

use Akeneo\Connectivity\Connection\Application\Apps\Notifier\AuthorizationRequestNotifierInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindAllUsernamesWithAclQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Notifier\AuthorizationRequestNotifier;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizationRequestNotifierTest extends TestCase
{
    private FindAllUsernamesWithAclQueryInterface|MockObject $findAllUsernamesWithAclQuery;
    private NotifierInterface|MockObject $notifier;
    private AuthorizationRequestNotifier $sut;

    protected function setUp(): void
    {
        $this->findAllUsernamesWithAclQuery = $this->createMock(FindAllUsernamesWithAclQueryInterface::class);
        $this->notifier = $this->createMock(NotifierInterface::class);
        $this->sut = new AuthorizationRequestNotifier(
            $this->findAllUsernamesWithAclQuery,
            $this->notifier,
        );
    }

    public function test_it_is_authorization_request_notifier(): void
    {
        $this->assertInstanceOf(AuthorizationRequestNotifier::class, $this->sut);
        $this->assertInstanceOf(AuthorizationRequestNotifierInterface::class, $this->sut);
    }

    public function test_it_notifies_all_users_that_can_manage_apps(): void
    {
        $usersThatShouldBeNotified = ['userA', 'userB', 'userC'];
        $this->findAllUsernamesWithAclQuery->method('execute')->with('akeneo_connectivity_connection_manage_apps')->willReturn($usersThatShouldBeNotified);
        $this->sut->notify(new ConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'App A',
            ['scope A1'],
            'connectionCodeA',
            'http://www.example.com/path/to/logo/a',
            'author A',
            'app_123456abcdef',
            'username_1234',
            ['category A1', 'category A2'],
            false,
            'partner A',
            true
        ));
        $this->notifier->method('notify')->with($this->isInstanceOf(NotificationInterface::class), $usersThatShouldBeNotified);
    }
}
