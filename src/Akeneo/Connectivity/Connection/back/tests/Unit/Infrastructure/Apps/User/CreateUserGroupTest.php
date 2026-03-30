<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\User;

use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\User\CreateUserGroup;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserGroupTest extends TestCase
{
    private SimpleFactoryInterface|MockObject $userGroupFactory;
    private ObjectUpdaterInterface|MockObject $userGroupUpdater;
    private SaverInterface|MockObject $userGroupSaver;
    private ValidatorInterface|MockObject $validator;
    private CreateUserGroup $sut;

    protected function setUp(): void
    {
        $this->userGroupFactory = $this->createMock(SimpleFactoryInterface::class);
        $this->userGroupUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->userGroupSaver = $this->createMock(SaverInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->sut = new CreateUserGroup($this->userGroupFactory, $this->userGroupUpdater, $this->userGroupSaver, $this->validator);
    }

    public function test_it_is_a_create_user_group(): void
    {
        $this->assertInstanceOf(CreateUserGroup::class, $this->sut);
        $this->assertInstanceOf(CreateUserGroupInterface::class, $this->sut);
    }

    public function test_it_creates_a_user_group(): void
    {
        $group = new Group();
        $this->userGroupFactory->method('create')->willReturn($group);
        $violations = new ConstraintViolationList([]);
        $this->validator->method('validate')->with($group)->willReturn($violations);
        $this->userGroupSaver->expects($this->once())->method('save')->with($group);
        $this->assertSame($group, $this->sut->execute('NEW GROUP NAME'));
    }

    public function test_it_does_not_create_an_invalid_group(): void
    {
        $group = new Group();
        $this->userGroupFactory->method('create')->willReturn($group);
        $violation1 = new ConstraintViolation(
            'an_error',
            '',
            [],
            '',
            'a_path',
            'invalid'
        );
        $violation2 = new ConstraintViolation(
            'an_error2',
            '',
            [],
            '',
            'a_path2',
            'invalid'
        );
        $violations = new ConstraintViolationList([$violation1, $violation2]);
        $this->validator->method('validate')->with($group)->willReturn($violations);
        $this->userGroupSaver->expects($this->never())->method('save')->with($group);
        $this->expectException(\LogicException::class);

        $this->expectExceptionMessage('The user group creation failed :\na_path: an_error\na_path2: an_error2');
        $this->sut->execute('NEW GROUP NAME');
    }
}
