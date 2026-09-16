<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\CustomApps\DTO\GetAllCustomAppsResult;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetAllCustomAppsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\Internal\GetAllCustomAppsAction;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllCustomAppsActionTest extends TestCase
{
    private GetAllCustomAppsQueryInterface|MockObject $getAllCustomAppsQuery;
    private GetAllCustomAppsAction $sut;

    protected function setUp(): void
    {
        $this->getAllCustomAppsQuery = $this->createMock(GetAllCustomAppsQueryInterface::class);
        $this->sut = new GetAllCustomAppsAction(
            new AppUrlGenerator(new PimUrl('http://httpd')),
            $this->getAllCustomAppsQuery,
        );
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(false);

        $this->getAllCustomAppsQuery->expects($this->never())->method('execute');

        $this->assertEquals(new RedirectResponse('/'), $this->sut->__invoke($request));
    }

    public function test_it_returns_the_custom_apps_with_the_pim_url_appended_to_the_activate_url(): void
    {
        $this->getAllCustomAppsQuery->method('execute')->willReturn(GetAllCustomAppsResult::create(1, [
            App::fromCustomAppValues([
                'id' => '6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9',
                'name' => 'App prototype',
                'author' => 'Julia Stark',
                'activate_url' => 'https://custom-app.example.com/activate',
                'callback_url' => 'https://custom-app.example.com/callback',
                'connected' => false,
            ]),
        ]));

        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(true);

        $response = $this->sut->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $payload = \json_decode((string) $response->getContent(), true);

        $this->assertSame(1, $payload['total']);
        $this->assertCount(1, $payload['apps']);
        $this->assertSame('App prototype', $payload['apps'][0]['name']);
        $this->assertSame(
            'https://custom-app.example.com/activate?pim_url=http%3A%2F%2Fhttpd',
            $payload['apps'][0]['activate_url'],
        );
    }

    public function test_it_flags_the_custom_apps_that_are_already_connected(): void
    {
        $this->getAllCustomAppsQuery->method('execute')->willReturn(GetAllCustomAppsResult::create(2, [
            App::fromCustomAppValues([
                'id' => 'not_connected_yet',
                'name' => 'App prototype',
                'activate_url' => 'https://custom-app.example.com/activate',
                'callback_url' => 'https://custom-app.example.com/callback',
                'connected' => false,
            ]),
            App::fromCustomAppValues([
                'id' => 'already_connected',
                'name' => 'Another app',
                'activate_url' => 'https://another-app.example.com/activate',
                'callback_url' => 'https://another-app.example.com/callback',
                'connected' => true,
            ]),
        ]));

        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(true);

        $payload = \json_decode((string) $this->sut->__invoke($request)->getContent(), true);

        $this->assertFalse($payload['apps'][0]['connected']);
        $this->assertTrue($payload['apps'][1]['connected']);
        $this->assertTrue($payload['apps'][0]['isCustomApp']);
    }

    public function test_it_returns_an_empty_result_when_no_custom_app_exists(): void
    {
        $this->getAllCustomAppsQuery->method('execute')->willReturn(GetAllCustomAppsResult::create(0, []));

        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(true);

        $payload = \json_decode((string) $this->sut->__invoke($request)->getContent(), true);

        $this->assertSame(0, $payload['total']);
        $this->assertSame([], $payload['apps']);
    }
}
