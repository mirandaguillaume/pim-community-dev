<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\IdentifierGeneratorShouldExist;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\IdentifierGeneratorShouldExistValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierGeneratorShouldExistValidatorTest extends TestCase
{
    private IdentifierGeneratorRepository|MockObject $identifierGeneratorRepository;
    private ExecutionContext|MockObject $context;
    private IdentifierGeneratorShouldExistValidator $sut;

    protected function setUp(): void
    {
        $this->identifierGeneratorRepository = $this->createMock(IdentifierGeneratorRepository::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new IdentifierGeneratorShouldExistValidator($this->identifierGeneratorRepository);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(IdentifierGeneratorShouldExistValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate('code', new NotBlank());
    }

    public function test_it_should_not_validate_something_else_than_an_update_generator_command(): void
    {
        $this->identifierGeneratorRepository->expects($this->never())->method('get')->with($this->anything());
        $this->sut->validate(new \stdClass(), new IdentifierGeneratorShouldExist());
    }

    public function test_it_should_build_violation_when_code_attribute_does_not_exist(): void
    {
        $this->context->expects($this->once())->method('buildViolation')->with(
            'validation.update.identifier_generator_code_not_found',
            ['{{code}}' => 'non_existing_generator']
        );
        $this->identifierGeneratorRepository->expects($this->once())->method('get')->with('non_existing_generator')->willThrowException(new CouldNotFindIdentifierGeneratorException('non_existing_generator'));
        $updateGeneratorCommand = new UpdateGeneratorCommand(
            'non_existing_generator',
            [],
            [['type' => 'unknown', 'string' => 'abcdef']],
            ['fr' => 'Générateur'],
            'sku',
            '-',
            'no',
        );
        $this->sut->validate($updateGeneratorCommand, new IdentifierGeneratorShouldExist());
    }

    public function test_it_should_be_valid_when_code_attribute_exist(): void
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
            IdentifierGeneratorCode::fromString('mygenerator'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
            TextTransformation::fromString('no'),
        );
        $this->identifierGeneratorRepository->expects($this->once())->method('get')->with('mygenerator')->willReturn($identifierGenerator);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $updateGeneratorCommand = new UpdateGeneratorCommand(
            'mygenerator',
            [],
            [['type' => 'unknown', 'string' => 'abcdef']],
            ['fr' => 'Générateur'],
            'sku',
            '-',
            'no',
        );
        $this->sut->validate($updateGeneratorCommand, new IdentifierGeneratorShouldExist());
    }
}
