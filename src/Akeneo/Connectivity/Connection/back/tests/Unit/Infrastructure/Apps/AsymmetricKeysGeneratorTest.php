<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\AsymmetricKeysGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AsymmetricKeysGeneratorTest extends TestCase
{
    private AsymmetricKeysGenerator $sut;

    protected function setUp(): void
    {
        $this->sut = new AsymmetricKeysGenerator(__DIR__ . '/openssl.cnf');
    }

    public function test_it_is_an_asymmetric_keys_generator(): void
    {
        $this->assertInstanceOf(AsymmetricKeysGenerator::class, $this->sut);
    }

    public function test_it_generates_asymmetric_keys(): void
    {
        $this->assertInstanceOf(AsymmetricKeys::class, $this->sut->generate());
    }
}
