<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretCommand;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateConnectionSecretCommandTest extends TestCase
{
    private RegenerateConnectionSecretCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new RegenerateConnectionSecretCommand('Magento');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(RegenerateConnectionSecretCommand::class, $this->sut);
    }

    public function test_it_returns_the_connection_code(): void
    {
        $this->assertSame('Magento', $this->sut->code());
    }
}
