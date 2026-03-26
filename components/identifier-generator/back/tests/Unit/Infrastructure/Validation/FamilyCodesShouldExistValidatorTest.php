<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyCodesShouldExist;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyCodesShouldExistValidator;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyCodesShouldExistValidatorTest extends TestCase
{
    private FindFamilyCodes|MockObject $findFamilyCodes;
    private ExecutionContext|MockObject $executionContext;
    private FamilyCodesShouldExistValidator $sut;

    protected function setUp(): void
    {
        $this->findFamilyCodes = $this->createMock(FindFamilyCodes::class);
        $this->executionContext = $this->createMock(ExecutionContext::class);
        $this->sut = new FamilyCodesShouldExistValidator($this->findFamilyCodes);
        $this->sut->initialize($this->executionContext);
        $this->findFamilyCodes->method('fromQuery')->with($this->anything())->willReturn(['shirts']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FamilyCodesShouldExistValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(['shirts'], new NotBlank());
    }

    public function test_it_should_not_validate_if_family_codes_is_not_an_array(): void
    {
        $condition = 'foo';
        $this->executionContext->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($condition, new FamilyCodesShouldExist());
    }

    public function test_it_should_not_validate_if_family_codes_is_not_an_array_of_strings(): void
    {
        $condition = ['shirts', true];
        $this->executionContext->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($condition, new FamilyCodesShouldExist());
    }

    public function test_it_should_not_build_violation_if_families_exist(): void
    {
        $condition = ['shirts'];
        $this->executionContext->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($condition, new FamilyCodesShouldExist());
    }

    public function test_it_should_build_violation_if_families_do_not_exist(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $condition = ['shirts', 'unknown_family1', 'unknown_family2'];
        $this->executionContext->expects($this->once())->method('buildViolation')->with($this->anything(), ['{{ familyCodes }}' => '"unknown_family1", "unknown_family2"'])->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($condition, new FamilyCodesShouldExist());
    }
}
