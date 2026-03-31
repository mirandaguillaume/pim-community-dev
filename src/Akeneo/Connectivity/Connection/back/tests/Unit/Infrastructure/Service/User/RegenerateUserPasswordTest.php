<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Service\User;

use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateUserPasswordInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Infrastructure\Service\User\RegenerateUserPassword;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateUserPasswordTest extends TestCase
{
    private UserManager|MockObject $userManager;
    private DbalConnection|MockObject $dbalConnection;
    private RegenerateUserPassword $sut;

    protected function setUp(): void
    {
        $this->userManager = $this->createMock(UserManager::class);
        $this->dbalConnection = $this->createMock(DbalConnection::class);
        $this->sut = new RegenerateUserPassword($this->userManager, $this->dbalConnection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(RegenerateUserPassword::class, $this->sut);
        $this->assertInstanceOf(RegenerateUserPasswordInterface::class, $this->sut);
    }

    public function test_it_regenerates_a_user_password(): void
    {
        $user = $this->createMock(UserInterface::class);

        $userId = new UserId(1);
        $this->userManager->method('findUserBy')->with(['id' => $userId->id()])->willReturn($user);
        $user->expects($this->once())->method('setPlainPassword')->with($this->isType('string'));
        $this->userManager->expects($this->once())->method('updateUser')->with($user);
        $executedStatements = [];
        $this->dbalConnection->expects($this->exactly(2))->method('executeStatement')->willReturnCallback(
            function (string $sql, array $params) use (&$executedStatements) {
                $executedStatements[] = $sql;
                return 0;
            }
        );
        $this->sut->execute($userId);
    }

    public function test_it_throws_an_exception_if_user_not_found(): void
    {
        $userId = new UserId(1);
        $this->userManager->method('findUserBy')->with(['id' => $userId->id()])->willReturn(null);
        $this->userManager->expects($this->never())->method('updateUser')->with($this->anything());
        $this->dbalConnection->expects($this->never())->method('executeStatement')->with($this->anything());
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('User with id "1" not found.');
        $this->sut->execute($userId);
    }
}
