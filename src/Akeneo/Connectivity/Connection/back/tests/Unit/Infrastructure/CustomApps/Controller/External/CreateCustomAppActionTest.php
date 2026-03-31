<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommandHandler;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppSecretQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External\CreateCustomAppAction;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateCustomAppActionTest extends TestCase
{
    private SecurityFacade|MockObject $security;
    private ValidatorInterface|MockObject $validator;
    private TranslatorInterface|MockObject $translator;
    private TokenStorageInterface|MockObject $tokenStorage;
    private CreateCustomAppCommandHandler|MockObject $createCustomAppCommandHandler;
    private GetCustomAppSecretQueryInterface|MockObject $getCustomAppSecretQuery;
    private CreateCustomAppAction $sut;

    protected function setUp(): void
    {
        $this->security = $this->createMock(SecurityFacade::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->createCustomAppCommandHandler = $this->createMock(CreateCustomAppCommandHandler::class);
        $this->getCustomAppSecretQuery = $this->createMock(GetCustomAppSecretQueryInterface::class);
        $this->sut = new CreateCustomAppAction(
            $this->security,
            $this->validator,
            $this->translator,
            $this->tokenStorage,
            $this->createCustomAppCommandHandler,
            $this->getCustomAppSecretQuery,
        );
    }

    public function test_it_is_a_create_custom_app_action(): void
    {
        $this->assertInstanceOf(CreateCustomAppAction::class, $this->sut);
    }

    public function test_it_throws_an_access_denied_exception_when_connection_cannot_manage_custom_apps(): void
    {
        $request = $this->createMock(Request::class);

        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($request);
    }

    public function test_it_throws_a_bad_request_exception_when_token_storage_have_no_token(): void
    {
        $request = $this->createMock(Request::class);

        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $this->tokenStorage->method('getToken')->willReturn(null);
        $this->expectException(BadRequestHttpException::class);

        $this->expectExceptionMessage('Invalid user token.');
        $this->sut->__invoke($request);
    }

    public function test_it_throws_a_bad_request_exception_when_no_valid_user_found(): void
    {
        $request = $this->createMock(Request::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(SymfonyUserInterface::class);

        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->expectException(BadRequestHttpException::class);

        $this->expectExceptionMessage('Invalid user token.');
        $this->sut->__invoke($request);
    }

    public function test_it_returns_a_list_of_errors_when_submit_data_is_invalid(): void
    {
        $request = $this->createMock(Request::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $user->method('getId')->willReturn(42);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->validator->method('validate')->with($this->isInstanceOf(CreateCustomAppCommand::class))->willReturn(new ConstraintViolationList([
                            new ConstraintViolation('Too long', '', [], '', 'name', 'it is too long'),
                            new ConstraintViolation('Not url', '', [], '', 'callbackUrl', 'it is not a url'),
                            new ConstraintViolation('Not url', '', [], '', 'activateUrl', 'it is not a url'),
                        ]));
        $this->translator->method('trans')->willReturnArgument(0);
        $request->method('get')->willReturnMap([
            ['name', '', 'Too long'],
            ['activate_url', '', 420],
            ['callback_url', '', 'Not url'],
        ]);
        $this->assertEquals(new JsonResponse(
            [
                        'errors' => [
                            [
                                'propertyPath' => 'name',
                                'message' => 'Too long',
                            ],
                            [
                                'propertyPath' => 'callback_url',
                                'message' => 'Not url',
                            ],
                            [
                                'propertyPath' => 'activate_url',
                                'message' => 'Not url',
                            ],
                        ],
                    ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ), $this->sut->__invoke($request));
    }

    public function test_it_fails_retrieve_the_custom_app_secret(): void
    {
        $request = $this->createMock(Request::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $user->method('getId')->willReturn(42);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->validator->method('validate')->with($this->isInstanceOf(CreateCustomAppCommand::class))->willReturn(new ConstraintViolationList());
        $this->createCustomAppCommandHandler->expects($this->once())->method('handle')->with($this->isInstanceOf(CreateCustomAppCommand::class));
        $this->getCustomAppSecretQuery->method('execute')->with($this->isType('string'))->willReturn(null);
        $request->method('get')->willReturnMap([
            ['name', '', 'CustomApp'],
            ['activate_url', '', 'http://callback-url.test'],
            ['callback_url', '', 'http://activate-url.test'],
        ]);
        $this->assertEquals(new JsonResponse(
            [
                        'errors' => [
                            'propertyPath' => null,
                            'message' => 'The client secret can not be retrieved.',
                        ],
                    ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ), $this->sut->__invoke($request));
    }

    public function test_it_creates_a_custom_app(): void
    {
        $request = $this->createMock(Request::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $user->method('getId')->willReturn(42);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->validator->method('validate')->with($this->isInstanceOf(CreateCustomAppCommand::class))->willReturn(new ConstraintViolationList());
        $this->createCustomAppCommandHandler->expects($this->once())->method('handle')->with($this->isInstanceOf(CreateCustomAppCommand::class));
        $this->getCustomAppSecretQuery->method('execute')->with($this->isType('string'))->willReturn('app_secret');
        $request->method('get')->willReturnMap([
            ['name', '', 'CustomApp'],
            ['activate_url', '', 'http://callback-url.test'],
            ['callback_url', '', 'http://activate-url.test'],
        ]);
        $response = $this->sut->__invoke($request);
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
        $content = \json_decode($response->getContent(), true);
        $this->assertArrayHasKey('client_id', $content);
        $this->assertSame('app_secret', $content['client_secret'] ?? null);
    }
}
