<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Settings\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UserTest extends TestCase
{
    private User $sut;

    protected function setUp(): void
    {
        $this->sut = new User(
            42,
            'magento',
            'my_password'
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(User::class, $this->sut);
    }

    public function test_it_returns_the_id(): void
    {
        $this->assertSame(42, $this->sut->id());
    }

    public function test_it_returns_the_username(): void
    {
        $this->assertSame('magento', $this->sut->username());
    }

    public function test_it_returns_the_password(): void
    {
        $this->assertSame('my_password', $this->sut->password());
    }
}
