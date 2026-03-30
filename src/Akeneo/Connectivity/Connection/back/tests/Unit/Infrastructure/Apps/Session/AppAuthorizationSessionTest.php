<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Session;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Session\AppAuthorizationSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AppAuthorizationSessionTest extends TestCase
{
    private RequestStack|MockObject $requestStack;
    private AppAuthorizationSession $sut;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->sut = new AppAuthorizationSession($this->requestStack);
    }

    public function test_it_is_an_app_authorization_session(): void
    {
        $this->assertInstanceOf(AppAuthorizationSession::class, $this->sut);
    }

    public function test_it_adds_in_the_session_the_app(): void
    {
        $session = $this->createMock(SessionInterface::class);

        $this->requestStack->method('getSession')->willReturn($session);
        $appAuthorization = AppAuthorization::createFromNormalized([
                    'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                    'authorization_scope' => 'write_catalog_structure delete_products read_association_types',
                    'authentication_scope' => 'openid profile email',
                    'redirect_uri' => 'http://example.com',
                    'state' => 'foo',
                ]);
        $session->expects($this->once())->method('set')->with(
            '_app_auth_90741597-54c5-48a1-98da-a68e7ee0a715',
            \json_encode($appAuthorization->normalize(), JSON_THROW_ON_ERROR)
        );
        $this->sut->initialize($appAuthorization);
    }

    public function test_it_retrieves_an_app_from_the_session_given_an_app_client_id(): void
    {
        $session = $this->createMock(SessionInterface::class);

        $this->requestStack->method('getSession')->willReturn($session);
        $appAuthorization = AppAuthorization::createFromNormalized([
                    'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                    'authorization_scope' => 'write_catalog_structure delete_products read_association_types',
                    'authentication_scope' => 'openid profile email',
                    'redirect_uri' => 'http://example.com',
                    'state' => 'foo',
                ]);
        $session->method('get')->with('_app_auth_90741597-54c5-48a1-98da-a68e7ee0a715')->willReturn(\json_encode([
                    'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                    'authorization_scope' => 'write_catalog_structure delete_products read_association_types',
                    'authentication_scope' => 'openid profile email',
                    'redirect_uri' => 'http://example.com',
                    'state' => 'foo',
                ]));
        $this->assertEquals($appAuthorization, $this->sut->getAppAuthorization($appAuthorization->clientId));
    }

    public function test_it_returns_null_if_no_app_has_been_initialized_before(): void
    {
        $session = $this->createMock(SessionInterface::class);

        $this->requestStack->method('getSession')->willReturn($session);
        $session->method('get')->with('_app_auth_90741597-54c5-48a1-98da-a68e7ee0a715')->willReturn(null);
        $this->assertNull($this->sut->getAppAuthorization('90741597-54c5-48a1-98da-a68e7ee0a715'));
    }
}
