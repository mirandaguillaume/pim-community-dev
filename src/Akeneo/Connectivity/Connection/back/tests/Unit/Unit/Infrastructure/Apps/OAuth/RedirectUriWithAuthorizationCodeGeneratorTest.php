<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\AuthorizationCodeGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGenerator;

class RedirectUriWithAuthorizationCodeGeneratorTest extends TestCase
{
    private AuthorizationCodeGeneratorInterface|MockObject $authorizationCodeGenerator;
    private RedirectUriWithAuthorizationCodeGenerator $sut;

    protected function setUp(): void
    {
        $this->authorizationCodeGenerator = $this->createMock(AuthorizationCodeGeneratorInterface::class);
        $this->sut = new RedirectUriWithAuthorizationCodeGenerator($this->authorizationCodeGenerator);
    }

    public function test_it_generates_a_redirect_uri_with_an_authorization_code(): void
    {
        $appAuthorization = $this->createMock(AppAuthorization::class);
        $appConfirmation = $this->createMock(AppConfirmation::class);

        $code = 'MjE3NTE3Y';
        $redirectUriWithoutCode = 'https://foo.example.com/oauth/callback';
        $pimUserId = 1;
        $appAuthorization->method('getRedirectUri')->willReturn($redirectUriWithoutCode);
        $appAuthorization->method('getState')->willReturn(null);
        $this->authorizationCodeGenerator->method('generate')->with(
            $appConfirmation,
            $pimUserId,
            $redirectUriWithoutCode
        )->willReturn($code);
        $this->assertSame('https://foo.example.com/oauth/callback?code=MjE3NTE3Y', $this->sut->generate($appAuthorization, $appConfirmation, $pimUserId));
    }

    public function test_it_generates_a_redirecturi_with_an_authorization_code_and_a_state(): void
    {
        $appAuthorization = $this->createMock(AppAuthorization::class);
        $appConfirmation = $this->createMock(AppConfirmation::class);

        $code = 'MjE3NTE3Y';
        $state = 'NzFkOGRhOG';
        $redirectUriWithoutCode = 'https://foo.example.com/oauth/callback';
        $pimUserId = 1;
        $appAuthorization->method('getRedirectUri')->willReturn($redirectUriWithoutCode);
        $appAuthorization->method('getState')->willReturn($state);
        $this->authorizationCodeGenerator->method('generate')->with(
            $appConfirmation,
            $pimUserId,
            $redirectUriWithoutCode
        )->willReturn($code);
        $this->assertSame('https://foo.example.com/oauth/callback?code=MjE3NTE3Y&state=NzFkOGRhOG', $this->sut->generate($appAuthorization, $appConfirmation, $pimUserId));
    }
}
