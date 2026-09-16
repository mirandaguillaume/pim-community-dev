<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\CustomApps\Service;

use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Service\GetCustomAppsNumberLimit;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCustomAppsNumberLimitTest extends TestCase
{
    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetCustomAppsNumberLimit::class, new GetCustomAppsNumberLimit(5));
    }

    public function test_it_exposes_the_configured_limit(): void
    {
        $sut = new GetCustomAppsNumberLimit(15);

        $this->assertSame(15, $sut->getLimit());
    }

    public function test_it_overrides_the_configured_limit(): void
    {
        $sut = new GetCustomAppsNumberLimit(15);
        $sut->setLimit(2);

        $this->assertSame(2, $sut->getLimit());
    }
}
