<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Create;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\CommandValidatorInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateGeneratorHandlerTest extends TestCase
{
    private IdentifierGeneratorRepository|MockObject $identifierGeneratorRepository;
    private CommandValidatorInterface|MockObject $validator;
    private CreateGeneratorHandler $sut;

    protected function setUp(): void
    {
        $this->identifierGeneratorRepository = $this->createMock(IdentifierGeneratorRepository::class);
        $this->validator = $this->createMock(CommandValidatorInterface::class);
        $this->sut = new CreateGeneratorHandler($this->identifierGeneratorRepository, $this->validator);
    }

    public function test_it_implements_create_generator_handler(): void
    {
        $this->assertInstanceOf(CreateGeneratorHandler::class, $this->sut);
    }

    public function test_it_must_call_save_repository(): void
    {
        $command = new CreateGeneratorCommand(
                    'abcdef',
                    [],
                    [['type' => 'free_text', 'string' => 'abcdef']],
                    ['fr' => 'Générateur'],
                    'sku',
                    '-',
                    'no',
                );
        $this->validator->expects($this->once())->method('validate')->with($command);
        $this->identifierGeneratorRepository->expects($this->once())->method('getNextId')->willReturn(IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'));
        $this->identifierGeneratorRepository->expects($this->once())->method('save')->with($this->isInstanceOf(IdentifierGenerator::class));
        $this->sut->__invoke($command);
    }
}
