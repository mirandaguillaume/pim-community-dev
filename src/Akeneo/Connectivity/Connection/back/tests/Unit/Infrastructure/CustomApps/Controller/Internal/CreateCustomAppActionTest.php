<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommandHandler;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppSecretQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\Internal\CreateCustomAppAction;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateCustomAppActionTest extends TestCase
{
    private FeatureFlag|MockObject $activateFeatureFlag;
    private ValidatorInterface|MockObject $validator;
    private TokenStorageInterface|MockObject $tokenStorage;
    private CreateCustomAppCommandHandler|MockObject $createCustomAppCommandHandler;
    private GetCustomAppSecretQueryInterface|MockObject $getCustomAppSecretQuery;
    private SecurityFacade|MockObject $security;
    private CreateCustomAppAction $sut;

    protected function setUp(): void
    {
        $this->activateFeatureFlag = $this->createMock(FeatureFlag::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->createCustomAppCommandHandler = $this->createMock(CreateCustomAppCommandHandler::class);
        $this->getCustomAppSecretQuery = $this->createMock(GetCustomAppSecretQueryInterface::class);
        $this->security = $this->createMock(SecurityFacade::class);
        $this->sut = new CreateCustomAppAction(
            $this->activateFeatureFlag,
            $this->validator,
            $this->tokenStorage,
            $this->createCustomAppCommandHandler,
            $this->getCustomAppSecretQuery,
            $this->security,
        );
    }

    public function test_it_is_a_create_custom_app_action(): void
    {
        $this->assertInstanceOf(CreateCustomAppAction::class, $this->sut);
    }

    public function test_it_answers_that_the_entity_has_not_been_created_because_the_secret_can_not_be_retrieved(): void
    {
        $request = $this->createMock(Request::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $this->activateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(42);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $request->method('get')->with('name', '')->willReturn('Custom app name');
        $request->method('get')->with('callbackUrl', '')->willReturn('http://callback-url.test');
        $request->method('get')->with('activateUrl', '')->willReturn('http://callback-url.test');
        $constraintList = new ConstraintViolationList([]);
        $this->validator->method('validate')->with($this->isInstanceOf(CreateCustomAppCommand::class))->willReturn($constraintList);
        $this->createCustomAppCommandHandler->expects($this->once())->method('handle')->with($this->isInstanceOf(CreateCustomAppCommand::class));
        $this->getCustomAppSecretQuery->method('execute')->with($this->isType('string'))->willReturn(null);
        $this->assertEquals(new JsonResponse(
            ['errors' => ['propertyPath' => null, 'message' => 'The client secret can not be retrieved.']],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ), $this->sut->__invoke($request));
    }

    public function test_it_answers_that_the_endpoint_does_not_exist_if_the_activate_feature_flag_is_disabled(): void
    {
        $request = $this->createMock(Request::class);

        $this->activateFeatureFlag->method('isEnabled')->willReturn(false);
        $this->expectException(NotFoundHttpException::class);
        $this->sut->__invoke($request);
    }

    public function test_it_redirects_to_the_root_if_the_request_does_not_come_from_ajax(): void
    {
        $request = $this->createMock(Request::class);

        $this->activateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(false);
        $this->assertEquals(new RedirectResponse('/'), $this->sut->__invoke($request));
    }

    public function test_it_answers_an_access_denied_if_the_endpoint_is_not_granted_to_the_user(): void
    {
        $request = $this->createMock(Request::class);

        $this->activateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($request);
    }

    public function test_it_answers_that_a_bad_request_has_been_done_because_the_token_does_not_exist(): void
    {
        $request = $this->createMock(Request::class);

        $this->activateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->tokenStorage->method('getToken')->willReturn(null);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $this->assertEquals(new JsonResponse(
            'Invalid user token.',
            Response::HTTP_BAD_REQUEST,
        ), $this->sut->__invoke($request));
    }

    public function test_it_answers_that_a_bad_request_has_been_done_because_the_user_does_not_exist(): void
    {
        $request = $this->createMock(Request::class);
        $token = $this->createMock(TokenInterface::class);

        $this->activateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn(null);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $this->assertEquals(new JsonResponse(
            'Invalid user token.',
            Response::HTTP_BAD_REQUEST,
        ), $this->sut->__invoke($request));
    }

    public function test_it_answers_that_the_entity_is_unprocessable_with_details_if_the_command_is_not_valid(): void
    {
        $request = $this->createMock(Request::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $this->activateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn(42);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_test_apps')->willReturn(true);
        $request->method('get')->with('name', '')->willReturn('Too long');
        $request->method('get')->with('callbackUrl', '')->willReturn('Not url');
        $request->method('get')->with('activateUrl', '')->willReturn('Not url');
        $nameViolation = new ConstraintViolation('Too long', '', [], '', 'name', 'it is too long');
        $callbackUrlViolation = new ConstraintViolation('Not url', '', [], '', 'callbackUrl', 'it is not a url');
        $activateUrlViolation = new ConstraintViolation('Not url', '', [], '', 'activateUrl', 'it is not a url');
        $constraintList = new ConstraintViolationList([$nameViolation, $callbackUrlViolation, $activateUrlViolation]);
        $this->validator->method('validate')->with($this->isInstanceOf(CreateCustomAppCommand::class))->willReturn($constraintList);
        $this->assertEquals(new JsonResponse(
            [
                        'errors' => [
                            [
                                'propertyPath' => 'name',
                                'message' => 'Too long',
                            ],
                            [
                                'propertyPath' => 'callbackUrl',
                                'message' => 'Not url',
                            ],
                            [
                                'propertyPath' => 'activateUrl',
                                'message' => 'Not url',
                            ],
                        ],
                    ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ), $this->sut->__invoke($request));
    }
}
