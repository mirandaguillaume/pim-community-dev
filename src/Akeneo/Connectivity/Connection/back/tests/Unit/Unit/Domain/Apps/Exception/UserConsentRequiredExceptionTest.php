<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Apps\Exception;

use Akeneo\Connectivity\Connection\Domain\Apps\Exception\UserConsentRequiredException;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserConsentRequiredExceptionTest extends TestCase
{
    private UserConsentRequiredException $sut;

    protected function setUp(): void
    {
        $this->sut = new UserConsentRequiredException('an_app_id', 1234);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(\Exception::class, $this->sut);
        $this->assertInstanceOf(UserConsentRequiredException::class, $this->sut);
    }

    public function test_it_gets_the_app_id(): void
    {
        $this->assertSame('an_app_id', $this->sut->getAppId());
    }

    public function test_it_gets_the_pim_user_id(): void
    {
        $this->assertSame(1234, $this->sut->getPimUserId());
    }
}
