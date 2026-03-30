<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionCommand;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteConnectionCommandTest extends TestCase
{
    private DeleteConnectionCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new DeleteConnectionCommand('magento');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DeleteConnectionCommand::class, $this->sut);
    }

    public function test_it_returns_the_code(): void
    {
        $this->assertSame('magento', $this->sut->code());
    }
}
