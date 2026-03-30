<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppsQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External\GetCustomAppsAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCustomAppsActionTest extends TestCase
{
    private TokenStorageInterface|MockObject $tokenStorage;
    private GetCustomAppsQueryInterface|MockObject $getCustomAppsQuery;
    private GetCustomAppsAction $sut;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->getCustomAppsQuery = $this->createMock(GetCustomAppsQueryInterface::class);
        $this->sut = new GetCustomAppsAction(
            $this->tokenStorage,
            $this->getCustomAppsQuery,
        );
    }

    public function test_it_is_a_get_custom_apps_action(): void
    {
        $this->assertInstanceOf(GetCustomAppsAction::class, $this->sut);
    }

    public function test_it_throws_a_bad_request_exception_when_token_storage_have_no_token(): void
    {
        $request = $this->createMock(Request::class);

        $this->tokenStorage->method('getToken')->willReturn(null);
        $this->expectException(new BadRequestHttpException('Invalid user token.'));
        $this->sut->__invoke($request);
    }
}
