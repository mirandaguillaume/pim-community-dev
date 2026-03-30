<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Service\User;

use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User as ReadUser;
use Akeneo\Connectivity\Connection\Infrastructure\Service\User\CreateUser;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserTest extends TestCase
{
    private SimpleFactoryInterface|MockObject $userFactory;
    private ObjectUpdaterInterface|MockObject $userUpdater;
    private ValidatorInterface|MockObject $validator;
    private SaverInterface|MockObject $userSaver;
    private CreateUser $sut;

    protected function setUp(): void
    {
        $this->userFactory = $this->createMock(SimpleFactoryInterface::class);
        $this->userUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->userSaver = $this->createMock(SaverInterface::class);
        $this->sut = new CreateUser($this->userFactory, $this->userUpdater, $this->validator, $this->userSaver);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CreateUser::class, $this->sut);
        $this->assertInstanceOf(CreateUserInterface::class, $this->sut);
    }

    public function test_it_creates_a_user(): void
    {
        $user = $this->createMock(User::class);

        $this->userFactory->method('create')->willReturn($user);
        $this->userUpdater->expects($this->once())->method('update')->with(/* TODO: convert Argument matcher */ $user, Argument::size(5));
        $violations = new ConstraintViolationList([]);
        $this->validator->method('validate')->with($user)->willReturn($violations);
        $this->userSaver->expects($this->once())->method('save')->with($user);
        $user->method('getId')->willReturn(1);
        $user->expects($this->once())->method('defineAsApiUser');
        $readUser = $this->execute('foo', 'bar', 'baz');
        $readUser->shouldBeAnInstanceOf(ReadUser::class);
        $readUser->id()->shouldReturn(1);
        $readUser->username()->shouldBeString();
        $readUser->password()->shouldBeString();
    }

    public function test_it_prevents_to_create_a_not_valid_user(): void
    {
        $user = $this->createMock(User::class);

        $this->userFactory->method('create')->willReturn($user);
        $this->userUpdater->expects($this->once())->method('update')->with(/* TODO: convert Argument matcher */ $user, Argument::size(5));
        $violations = new ConstraintViolationList([
                    new ConstraintViolation('wrong', 'wrong', [], 'wrong', 'path', 'wrong'),
                    new ConstraintViolation('wrong2', 'wrong2', [], 'wrong2', 'path2', 'wrong2'),
                ]);
        $this->validator->method('validate')->with($user)->willReturn($violations);
        $this->userSaver->expects($this->never())->method('save')->with($this->anything());
        $this->expectException(new \LogicException(
            'The user creation failed :' . PHP_EOL
                            . 'path: wrong' . PHP_EOL
                            . 'path2: wrong2'
        ));
        $this->sut->execute('foo', 'bar', 'baz');
    }
}
