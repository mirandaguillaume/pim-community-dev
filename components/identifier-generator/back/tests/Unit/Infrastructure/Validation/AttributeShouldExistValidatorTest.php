<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AttributeShouldExist;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AttributeShouldExistValidator;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeShouldExistValidatorTest extends TestCase
{
    private GetAttributes|MockObject $getAttributes;
    private ExecutionContext|MockObject $context;
    private AttributeShouldExistValidator $sut;

    protected function setUp(): void
    {
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new AttributeShouldExistValidator($this->getAttributes);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AttributeShouldExistValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate('code', new NotBlank());
    }

    public function test_it_should_build_violation_when_attribute_does_not_exist(): void
    {
        $this->context->expects($this->once())->method('buildViolation')->with(
            'validation.identifier_generator.attribute_does_not_exist',
            ['{{code}}' => 'sku']
        );
        $this->sut->validate('sku', new AttributeShouldExist());
    }

    public function test_it_should_be_valid_when_attribute_exist(): void
    {
        $this->getAttributes->expects($this->once())->method('forCode')->with('sku')->willReturn(new Attribute(
            'sku',
            AttributeTypes::IDENTIFIER,
            [],
            false,
            false,
            null,
            null,
            null,
            '',
            []
        ));
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('sku', new AttributeShouldExist());
    }
}
