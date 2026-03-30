<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public\RequestAccessTokenAction;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestAccessTokenActionTest extends TestCase
{
    private FeatureFlag|MockObject $featureFlag;
    private ValidatorInterface|MockObject $validator;
    private CreateAccessTokenInterface|MockObject $createAccessToken;
    private RequestAccessTokenAction $sut;

    protected function setUp(): void
    {
        $this->featureFlag = $this->createMock(FeatureFlag::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->createAccessToken = $this->createMock(CreateAccessTokenInterface::class);
        $this->sut = new RequestAccessTokenAction(
            $this->featureFlag,
            $this->validator,
            $this->createAccessToken,
        );
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        $request = $this->createMock(Request::class);

        $this->featureFlag->method('isEnabled')->willReturn(false);
        $this->expectException(NotFoundHttpException::class);
        $this->sut->__invoke($request, 'foo');
    }

    public function test_it_returns_a_bad_request_response_when_access_token_request_is_invalid(): void
    {
        $request = $this->createMock(Request::class);
        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $constraintViolation = $this->createMock(ConstraintViolationInterface::class);

        $this->featureFlag->method('isEnabled')->willReturn(true);
        $request->request = new InputBag([
                    'client_id' => 'some_client_id',
                    'code' => 'some_code',
                    'grant_type' => 'some_grant_type',
                    'code_identifier' => 'some_code_identifier',
                    'code_challenge' => 'some_code_challenge',
                ]);
        $constraintViolation->method('getMessage')->willReturn('invalid_grant');
        $constraintViolationList->method('count')->willReturn(1);
        $constraintViolationList->method('offsetGet')->with(0)->willReturn($constraintViolation);
        $this->validator->method('validate')->with($this->isInstanceOf(AccessTokenRequest::class))->willReturn($constraintViolationList);
        $this->assertEquals(new JsonResponse([
                    'error' => 'invalid_grant',
                ], Response::HTTP_BAD_REQUEST), $this->sut->__invoke($request));
    }

    public function test_it_returns_a_bad_request_response_with_error_description_when_code_challenge_is_expired(): void
    {
        $request = $this->createMock(Request::class);
        $constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $constraintViolation = $this->createMock(ConstraintViolation::class);

        $this->featureFlag->method('isEnabled')->willReturn(true);
        $request->request = new InputBag([
                    'client_id' => 'some_client_id',
                    'code' => 'some_code',
                    'grant_type' => 'some_grant_type',
                    'code_identifier' => 'some_code_identifier',
                    'code_challenge' => 'some_code_challenge',
                ]);
        $constraintViolation->method('getMessage')->willReturn('invalid_grant');
        $constraintViolation->method('getCause')->willReturn('Code is expired');
        $constraintViolationList->method('count')->willReturn(1);
        $constraintViolationList->method('offsetGet')->with(0)->willReturn($constraintViolation);
        $this->validator->method('validate')->with($this->isInstanceOf(AccessTokenRequest::class))->willReturn($constraintViolationList);
        $this->assertEquals(new JsonResponse([
                    'error' => 'invalid_grant',
                    'error_description' => 'Code is expired',
                ], Response::HTTP_BAD_REQUEST), $this->sut->__invoke($request));
    }
}
