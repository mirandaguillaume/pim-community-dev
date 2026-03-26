<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\IdentifierGeneratorCreationLimit;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\IdentifierGeneratorCreationLimitValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierGeneratorCreationLimitValidatorTest extends TestCase
{
    private IdentifierGeneratorRepository|MockObject $repository;
    private ExecutionContext|MockObject $context;
    private IdentifierGeneratorCreationLimitValidator $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(IdentifierGeneratorRepository::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new IdentifierGeneratorCreationLimitValidator($this->repository);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(IdentifierGeneratorCreationLimitValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate('code', new NotBlank());
    }

    public function test_it_should_build_violation_when_an_identifier_generator_already_exist(): void
    {
        $this->repository->expects($this->once())->method('count')->willReturn(1);
        $this->context->expects($this->once())->method('buildViolation')->with('validation.create.identifier_limit_reached',
                    ['{{limit}}' => 1]);
        $this->sut->validate('generatorCode', new IdentifierGeneratorCreationLimit());
    }

    public function test_it_should_build_violation_when_identifier_generator_limit_is_reached(): void
    {
        $this->repository->expects($this->once())->method('count')->willReturn(2);
        $this->context->expects($this->once())->method('buildViolation')->with('validation.create.identifier_limit_reached',
                    ['{{limit}}' => 2]);
        $this->sut->validate('generatorCode', new IdentifierGeneratorCreationLimit(['limit' => 2]));
    }

    public function test_it_should_be_valid_when_identifier_generator_is_under_limit(): void
    {
        $this->repository->expects($this->once())->method('count')->willReturn(1);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('generatorCode', new IdentifierGeneratorCreationLimit(['limit' => 2]));
    }
}
