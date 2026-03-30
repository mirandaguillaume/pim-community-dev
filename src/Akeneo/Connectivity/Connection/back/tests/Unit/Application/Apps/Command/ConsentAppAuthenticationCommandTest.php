<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsentAppAuthenticationCommandTest extends TestCase
{
    private ConsentAppAuthenticationCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new ConsentAppAuthenticationCommand('a_client_id', 1);
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(ConsentAppAuthenticationCommand::class, $this->sut);
    }

    public function test_it_gets_client_id(): void
    {
        $this->assertSame('a_client_id', $this->sut->getClientId());
    }

    public function test_it_gets_pim_user_id(): void
    {
        $this->assertSame(1, $this->sut->getPimUserId());
    }
}
