<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\IsConnectionsNumberLimitReached;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionsNumberLimitReachedTest extends TestCase
{
    private IsConnectionsNumberLimitReached $sut;

    protected function setUp(): void
    {
        $this->sut = new IsConnectionsNumberLimitReached();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(IsConnectionsNumberLimitReached::class, $this->sut);
    }

    public function test_it_is_a_constraint(): void
    {
        $this->assertInstanceOf(Constraint::class, $this->sut);
    }

    public function test_it_provides_a_target(): void
    {
        $this->assertSame(Constraint::CLASS_CONSTRAINT, $this->sut->getTargets());
    }

    public function test_it_has_a_message(): void
    {
        $this->sut->message->shouldBeString();
    }
}
