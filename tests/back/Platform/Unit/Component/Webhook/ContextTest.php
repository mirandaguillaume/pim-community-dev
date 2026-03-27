<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Component\Webhook;

use Akeneo\Platform\Component\Webhook\Context;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ContextTest extends TestCase
{
    private Context $sut;

    protected function setUp(): void
    {
        $this->sut = new Context('username_0000', 10, true);
    }

    public function test_it_is_a_context(): void
    {
        $this->assertInstanceOf(Context::class, $this->sut);
    }

    public function test_it_returns_a_username(): void
    {
        $this->assertSame('username_0000', $this->sut->getUsername());
    }

    public function test_it_returns_a_user_id(): void
    {
        $this->assertSame(10, $this->sut->getUserId());
    }

    public function test_it_returns_a_uuid_usage_status(): void
    {
        $this->assertSame(true, $this->sut->isUsingUuid());
    }
}
