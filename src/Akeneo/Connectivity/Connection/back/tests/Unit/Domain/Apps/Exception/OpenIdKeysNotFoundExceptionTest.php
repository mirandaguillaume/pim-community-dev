<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Apps\Exception;

use Akeneo\Connectivity\Connection\Domain\Apps\Exception\OpenIdKeysNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OpenIdKeysNotFoundExceptionTest extends TestCase
{
    private OpenIdKeysNotFoundException $sut;

    protected function setUp(): void
    {
        $this->sut = new OpenIdKeysNotFoundException();
        $this->sut->beConstructedWith();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(\Exception::class, $this->sut);
        $this->assertInstanceOf(OpenIdKeysNotFoundException::class, $this->sut);
    }
}
