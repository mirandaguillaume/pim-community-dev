<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordCommand;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre/jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateConnectionPasswordCommandTest extends TestCase
{
    private RegenerateConnectionPasswordCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new RegenerateConnectionPasswordCommand('Magento');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(RegenerateConnectionPasswordCommand::class, $this->sut);
    }

    public function test_it_returns_the_connection_code(): void
    {
        $this->assertSame('Magento', $this->sut->code());
    }
}
