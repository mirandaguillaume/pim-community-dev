<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppSecretQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\CodeChallengeMustBeValid;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Validation\CodeChallengeMustBeValidValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CodeChallengeMustBeValidValidatorTest extends TestCase
{
    private WebMarketplaceApiInterface|MockObject $webMarketplaceApi;
    private GetCustomAppSecretQueryInterface|MockObject $getCustomAppSecretQuery;
    private FeatureFlag|MockObject $fakeAppsFeatureFlag;
    private ExecutionContextInterface|MockObject $context;
    private CodeChallengeMustBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->webMarketplaceApi = $this->createMock(WebMarketplaceApiInterface::class);
        $this->getCustomAppSecretQuery = $this->createMock(GetCustomAppSecretQueryInterface::class);
        $this->fakeAppsFeatureFlag = $this->createMock(FeatureFlag::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new CodeChallengeMustBeValidValidator($this->webMarketplaceApi, $this->getCustomAppSecretQuery, $this->fakeAppsFeatureFlag);
        $this->fakeAppsFeatureFlag->method('isEnabled')->willReturn(false);
        $this->sut->initialize($this->context);
    }

    public function test_it_validates_only_the_correct_constraint(): void
    {
        $constraint = $this->createMock(Constraint::class);

        $this->expectException(UnexpectedTypeException::class);
        $this->sut->validate(
            null,
            $constraint,
        );
    }

    public function test_it_validates_only_an_access_token_request(): void
    {
        $constraint = $this->createMock(CodeChallengeMustBeValid::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(
            new \stdClass(),
            $constraint,
        );
    }

    public function test_it_validates_that_the_code_challenge_is_valid(): void
    {
        $constraint = $this->createMock(CodeChallengeMustBeValid::class);
        $value = $this->createMock(AccessTokenRequest::class);

        $clientId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'JN2eVHPP4F';
        $value->method('getClientId')->willReturn($clientId);
        $value->method('getCodeIdentifier')->willReturn($codeIdentifier);
        $value->method('getCodeChallenge')->willReturn($codeChallenge);
        $this->webMarketplaceApi->method('validateCodeChallenge')->with(
            $clientId,
            $codeIdentifier,
            $codeChallenge
        )->willReturn(true);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($value, $constraint);
    }

    public function test_it_skips_the_validator_if_a_value_is_empty(): void
    {
        $constraint = $this->createMock(CodeChallengeMustBeValid::class);
        $value = $this->createMock(AccessTokenRequest::class);

        $value->method('getClientId')->willReturn('');
        $value->method('getCodeIdentifier')->willReturn('');
        $value->method('getCodeChallenge')->willReturn('');
        $this->webMarketplaceApi->expects($this->never())->method('validateCodeChallenge');
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($value, $constraint);
    }

    public function test_it_adds_a_violation_when_the_code_challenge_is_refused(): void
    {
        $constraint = $this->createMock(CodeChallengeMustBeValid::class);
        $value = $this->createMock(AccessTokenRequest::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $clientId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'JN2eVHPP4F';
        $value->method('getClientId')->willReturn($clientId);
        $value->method('getCodeIdentifier')->willReturn($codeIdentifier);
        $value->method('getCodeChallenge')->willReturn($codeChallenge);
        $this->webMarketplaceApi->method('validateCodeChallenge')->with(
            $clientId,
            $codeIdentifier,
            $codeChallenge
        )->willReturn(false);
        $this->context->method('buildViolation')->with($this->anything())->willReturn($violation);
        $violation->method('atPath')->with('codeChallenge')->willReturn($violation);
        $violation->expects($this->once())->method('addViolation');
        $this->sut->validate($value, $constraint);
    }

    public function test_it_validates_that_the_custom_app_code_challenge_is_valid(): void
    {
        $constraint = $this->createMock(CodeChallengeMustBeValid::class);
        $value = $this->createMock(AccessTokenRequest::class);

        $clientId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $clientSecret = 'nDYbJo8X48fL';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = '6ffbb306c0ce4a545d2540c9303c10258c4e4c321c3899c5177fd94106e1b73d';
        $value->method('getClientId')->willReturn($clientId);
        $value->method('getCodeIdentifier')->willReturn($codeIdentifier);
        $value->method('getCodeChallenge')->willReturn($codeChallenge);
        $this->getCustomAppSecretQuery->method('execute')->with($clientId)->willReturn($clientSecret);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($value, $constraint);
    }

    public function test_it_adds_a_violation_when_the_custom_app_code_challenge_is_refused(): void
    {
        $constraint = $this->createMock(CodeChallengeMustBeValid::class);
        $value = $this->createMock(AccessTokenRequest::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $clientId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $clientSecret = 'nDYbJo8X48fL';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'invalid';
        $value->method('getClientId')->willReturn($clientId);
        $value->method('getCodeIdentifier')->willReturn($codeIdentifier);
        $value->method('getCodeChallenge')->willReturn($codeChallenge);
        $this->getCustomAppSecretQuery->method('execute')->with($clientId)->willReturn($clientSecret);
        $this->context->method('buildViolation')->with($this->anything())->willReturn($violation);
        $violation->method('atPath')->with('codeChallenge')->willReturn($violation);
        $violation->expects($this->once())->method('addViolation');
        $this->sut->validate($value, $constraint);
    }
}
