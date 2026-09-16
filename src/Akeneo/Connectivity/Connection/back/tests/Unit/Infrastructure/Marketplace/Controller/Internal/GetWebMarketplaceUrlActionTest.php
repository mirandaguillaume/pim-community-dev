<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Marketplace\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\Marketplace\MarketplaceUrlGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Controller\Internal\GetWebMarketplaceUrlAction;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetWebMarketplaceUrlActionTest extends TestCase
{
    private MarketplaceUrlGeneratorInterface|MockObject $marketplaceUrlGenerator;
    private UserContext|MockObject $userContext;
    private GetWebMarketplaceUrlAction $sut;

    protected function setUp(): void
    {
        $this->marketplaceUrlGenerator = $this->createMock(MarketplaceUrlGeneratorInterface::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->sut = new GetWebMarketplaceUrlAction(
            $this->marketplaceUrlGenerator,
            $this->userContext,
        );
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(false);

        $this->userContext->expects($this->never())->method('getUser');
        $this->marketplaceUrlGenerator
            ->expects($this->never())
            ->method('generateUrl')
            ->with($this->anything());

        $this->assertEquals(new RedirectResponse('/'), $this->sut->__invoke($request));
    }

    public function test_it_returns_the_marketplace_url_generated_for_the_current_user(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(true);

        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('julia');
        $this->userContext->method('getUser')->willReturn($user);

        $this->marketplaceUrlGenerator
            ->expects($this->once())
            ->method('generateUrl')
            ->with('julia')
            ->willReturn('https://marketplace.akeneo.com/extensions?user_profile=product_manager');

        $response = $this->sut->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(
            'https://marketplace.akeneo.com/extensions?user_profile=product_manager',
            \json_decode((string) $response->getContent(), true),
        );
    }

    public function test_it_generates_the_url_for_the_logged_in_user_and_not_for_another_one(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(true);

        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('peter');
        $this->userContext->method('getUser')->willReturn($user);

        $this->marketplaceUrlGenerator
            ->method('generateUrl')
            ->willReturnCallback(function (string $username): string {
                $this->assertSame('peter', $username);

                return \sprintf('https://marketplace.akeneo.com/extensions?username=%s', $username);
            });

        $response = $this->sut->__invoke($request);

        $this->assertSame(
            'https://marketplace.akeneo.com/extensions?username=peter',
            \json_decode((string) $response->getContent(), true),
        );
    }
}
