<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Job\Unit\Domain\Model;

use Akeneo\Platform\Job\Domain\Model\Status;
use PHPUnit\Framework\TestCase;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class StatusTest extends TestCase
{
    private Status $sut;

    protected function setUp(): void
    {
    }

    public function testItIsConstructableWithStatus(): void
    {
        $this->sut = Status::fromStatus(3);
        $this->assertSame(3, $this->sut->getStatus());
        $this->assertSame('IN_PROGRESS', $this->sut->getLabel());
    }

    public function testItIsConstructableWithLabel(): void
    {
        $this->sut = Status::fromLabel('IN_PROGRESS');
        $this->assertSame(3, $this->sut->getStatus());
        $this->assertSame('IN_PROGRESS', $this->sut->getLabel());
    }

    public function testItThrowsExceptionWhenTryingToConstructItWithInvalidStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Status::fromStatus(0);
        $this->expectException(\InvalidArgumentException::class);
        Status::fromStatus(26);
    }

    public function testItThrowsExceptionWhenTryingToConstructItWithInvalidLabel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Status::fromLabel('invalid');
    }
}
