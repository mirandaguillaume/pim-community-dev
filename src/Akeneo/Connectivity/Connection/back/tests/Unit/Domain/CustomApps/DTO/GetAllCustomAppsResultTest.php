<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\CustomApps\DTO;

use Akeneo\Connectivity\Connection\Domain\CustomApps\DTO\GetAllCustomAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllCustomAppsResultTest extends TestCase
{
    public function test_it_rejects_a_list_containing_something_else_than_an_app(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Expected an array of "%s", got "string".', App::class));

        GetAllCustomAppsResult::create(1, ['not_an_app']);
    }

    public function test_it_rejects_a_list_mixing_apps_and_other_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Expected an array of "%s", got "array".', App::class));

        GetAllCustomAppsResult::create(2, [$this->createApp('an_id'), ['id' => 'another_id']]);
    }

    public function test_it_normalizes_the_total_and_every_app(): void
    {
        $result = GetAllCustomAppsResult::create(2, [
            $this->createApp('first_id', 'App prototype'),
            $this->createApp('second_id', 'Another app'),
        ]);

        $normalized = $result->normalize();

        $this->assertSame(2, $normalized['total']);
        $this->assertCount(2, $normalized['apps']);
        $this->assertSame('first_id', $normalized['apps'][0]['id']);
        $this->assertSame('App prototype', $normalized['apps'][0]['name']);
        $this->assertSame('second_id', $normalized['apps'][1]['id']);
        $this->assertTrue($normalized['apps'][0]['isCustomApp']);
    }

    public function test_it_normalizes_an_empty_result(): void
    {
        $normalized = GetAllCustomAppsResult::create(0, [])->normalize();

        $this->assertSame(['total' => 0, 'apps' => []], $normalized);
    }

    public function test_it_appends_the_pim_url_query_parameters_to_every_activate_url(): void
    {
        $result = GetAllCustomAppsResult::create(2, [
            $this->createApp('first_id'),
            $this->createApp('second_id'),
        ]);

        $normalized = $result->withPimUrlSource(['pim_url' => 'http://httpd'])->normalize();

        $this->assertSame(2, $normalized['total']);
        $this->assertSame(
            'https://custom-app.example.com/activate?pim_url=http%3A%2F%2Fhttpd',
            $normalized['apps'][0]['activate_url'],
        );
        $this->assertSame(
            'https://custom-app.example.com/activate?pim_url=http%3A%2F%2Fhttpd',
            $normalized['apps'][1]['activate_url'],
        );
    }

    public function test_it_leaves_the_original_result_untouched_when_adding_the_pim_url_source(): void
    {
        $result = GetAllCustomAppsResult::create(1, [$this->createApp('an_id')]);

        $withPimUrl = $result->withPimUrlSource(['pim_url' => 'http://httpd']);

        $this->assertNotSame($result, $withPimUrl);
        $this->assertSame(
            'https://custom-app.example.com/activate',
            $result->normalize()['apps'][0]['activate_url'],
        );
    }

    public function test_it_keeps_a_total_that_differs_from_the_number_of_apps(): void
    {
        $normalized = GetAllCustomAppsResult::create(10, [$this->createApp('an_id')])->normalize();

        $this->assertSame(10, $normalized['total']);
        $this->assertCount(1, $normalized['apps']);
    }

    private function createApp(string $id, string $name = 'App prototype'): App
    {
        return App::fromCustomAppValues([
            'id' => $id,
            'name' => $name,
            'author' => 'Julia Stark',
            'activate_url' => 'https://custom-app.example.com/activate',
            'callback_url' => 'https://custom-app.example.com/callback',
        ]);
    }
}
