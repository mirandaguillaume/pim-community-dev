<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\User;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\User\CreateUser;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUserTest extends TestCase
{
    private const string USERNAME = 'app_prototype_6ff52991';
    private const string APP_ID = '6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e';

    private SimpleFactoryInterface|MockObject $userFactory;
    private ObjectUpdaterInterface|MockObject $userUpdater;
    private ValidatorInterface|MockObject $validator;
    private SaverInterface|MockObject $userSaver;
    private User|MockObject $user;
    private CreateUser $sut;
    private array $capturedPayload = [];

    protected function setUp(): void
    {
        $this->userFactory = $this->createMock(SimpleFactoryInterface::class);
        $this->userUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->userSaver = $this->createMock(SaverInterface::class);
        $this->user = $this->createMock(User::class);
        $this->userFactory->method('create')->willReturn($this->user);
        $this->sut = new CreateUser($this->userFactory, $this->userUpdater, $this->validator, $this->userSaver);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CreateUser::class, $this->sut);
        $this->assertInstanceOf(CreateUserInterface::class, $this->sut);
    }

    public function test_it_flags_the_created_user_as_an_api_user(): void
    {
        $this->givenAValidUser();

        $this->user->expects($this->once())->method('defineAsApiUser');

        $this->sut->execute(self::USERNAME, 'App prototype', ['app_group'], ['ROLE_APP'], self::APP_ID);
    }

    public function test_it_creates_the_user_carrying_the_app_role_and_returns_its_id(): void
    {
        $this->givenAValidUser();
        $this->userSaver->expects($this->once())->method('save')->with($this->user);

        $userId = $this->sut->execute(
            self::USERNAME,
            'App prototype',
            ['app_group'],
            ['ROLE_APP_PROTOTYPE'],
            self::APP_ID
        );

        $this->assertSame(42, $userId);
        $this->assertSame(self::USERNAME, $this->capturedPayload['username']);
        $this->assertSame('App prototype', $this->capturedPayload['first_name']);
        $this->assertSame(' ', $this->capturedPayload['last_name']);
        $this->assertSame(\sprintf('%s@example.com', self::USERNAME), $this->capturedPayload['email']);
        $this->assertSame(['app_group'], $this->capturedPayload['groups']);
        $this->assertSame(['ROLE_APP_PROTOTYPE'], $this->capturedPayload['roles']);
        $this->assertSame(['app_id' => self::APP_ID], $this->capturedPayload['properties']);
    }

    public function test_it_gives_the_user_a_generated_password(): void
    {
        $this->givenAValidUser();

        $this->sut->execute(self::USERNAME, 'App prototype', [], [], self::APP_ID);

        $this->assertIsString($this->capturedPayload['password']);
        $this->assertNotSame('', $this->capturedPayload['password']);
        $this->assertNotSame(self::USERNAME, $this->capturedPayload['password']);
    }

    public function test_it_does_not_save_an_invalid_user_and_reports_every_violation(): void
    {
        $this->userUpdater->expects($this->once())->method('update');
        $this->validator->method('validate')->with($this->user)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('This value should not be blank.', '', [], '', 'email', ''),
            new ConstraintViolation('This value is too long.', '', [], '', 'username', ''),
        ]));
        $this->userSaver->expects($this->never())->method('save')->with($this->anything());

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            "The user creation failed :\nemail: This value should not be blank.\nusername: This value is too long."
        );

        $this->sut->execute(self::USERNAME, 'App prototype', ['app_group'], ['ROLE_APP'], self::APP_ID);
    }

    private function givenAValidUser(): void
    {
        $this->user->method('getId')->willReturn(42);
        $this->userUpdater->expects($this->once())->method('update')->willReturnCallback(
            function (object $user, array $payload): void {
                $this->capturedPayload = $payload;
            }
        );
        $this->validator->method('validate')->with($this->user)->willReturn(new ConstraintViolationList([]));
    }
}
