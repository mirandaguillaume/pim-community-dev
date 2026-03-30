<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Apps\DTO;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AsymmetricKeysTest extends TestCase
{
    private AsymmetricKeys $sut;

    protected function setUp(): void
    {
        $this->sut = AsymmetricKeys::create('a_public_key', 'a_private_key');
    }

    public function test_it_is_an_asymmetric_keys(): void
    {
        $this->assertInstanceOf(AsymmetricKeys::class, $this->sut);
    }

    public function test_it_normalizes_an_asymmetric_keys(): void
    {
        $this->assertSame([
                    AsymmetricKeys::PUBLIC_KEY => 'a_public_key',
                    AsymmetricKeys::PRIVATE_KEY => 'a_private_key',
                ], $this->sut->normalize());
    }
}
