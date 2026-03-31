<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\AsymmetricKeysGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveAsymmetricKeysQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateAsymmetricKeysHandlerTest extends TestCase
{
    private AsymmetricKeysGeneratorInterface|MockObject $asymmetricKeysGenerator;
    private SaveAsymmetricKeysQueryInterface|MockObject $saveAsymmetricKeysQuery;
    private GenerateAsymmetricKeysHandler $sut;

    protected function setUp(): void
    {
        $this->asymmetricKeysGenerator = $this->createMock(AsymmetricKeysGeneratorInterface::class);
        $this->saveAsymmetricKeysQuery = $this->createMock(SaveAsymmetricKeysQueryInterface::class);
        $this->sut = new GenerateAsymmetricKeysHandler($this->asymmetricKeysGenerator, $this->saveAsymmetricKeysQuery);
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(GenerateAsymmetricKeysHandler::class, $this->sut);
    }

    public function test_it_generates_asymemtric_keys(): void
    {
        $keys = AsymmetricKeys::create('a_public_key', 'a_private_key');
        $this->asymmetricKeysGenerator->method('generate')->willReturn($keys);
        $this->saveAsymmetricKeysQuery->expects($this->once())->method('execute')->with($keys);
        $this->sut->handle(new GenerateAsymmetricKeysCommand());
    }
}
