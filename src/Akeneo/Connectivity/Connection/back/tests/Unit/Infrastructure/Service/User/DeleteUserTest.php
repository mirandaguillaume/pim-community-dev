<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Service\User;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Infrastructure\Service\User\DeleteUser;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteUserTest extends TestCase
{
    private UserRepositoryInterface|MockObject $repository;
    private RemoverInterface|MockObject $remover;
    private DeleteUser $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepositoryInterface::class);
        $this->remover = $this->createMock(RemoverInterface::class);
        $this->sut = new DeleteUser($this->repository, $this->remover);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DeleteUser::class, $this->sut);
        $this->assertInstanceOf(DeleteUserInterface::class, $this->sut);
    }

    public function test_it_deletes_a_user(): void
    {
        $user = $this->createMock(User::class);

        $userId = new UserId(1);
        $this->repository->method('find')->with($userId->id())->willReturn($user);
        $this->repository->expects($this->once())->method('find')->with($userId->id());
        $this->remover->expects($this->once())->method('remove')->with($user);
        $this->sut->execute($userId);
    }

    public function test_it_throws_an_exception_if_user_not_found(): void
    {
        $userId = new UserId(1);
        $this->repository->method('find')->with($userId->id())->willReturn(null);
        $this->remover->expects($this->never())->method('remove')->with($this->anything());
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('User with id "1" does not exist.');
        $this->sut->execute($userId);
    }
}
