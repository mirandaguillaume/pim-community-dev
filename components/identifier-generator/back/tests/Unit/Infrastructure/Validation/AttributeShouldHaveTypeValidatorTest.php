<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AttributeShouldHaveType;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AttributeShouldHaveTypeValidator;
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
class AttributeShouldHaveTypeValidatorTest extends TestCase
{
    private GetAttributes|MockObject $getAttributes;
    private ExecutionContext|MockObject $context;
    private AttributeShouldHaveTypeValidator $sut;

    protected function setUp(): void
    {
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new AttributeShouldHaveTypeValidator($this->getAttributes);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AttributeShouldHaveTypeValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate('code', new NotBlank());
    }

    public function test_it_should_build_violation_when_attribute_should_have_type(): void
    {
        $this->getAttributes->expects($this->once())->method('forCode')->with('sku')->willReturn(new Attribute(
            'sku',
            AttributeTypes::TEXT,
            [],
            false,
            false,
            null,
            null,
            null,
            '',
            []
        ));
        $this->context->expects($this->once())->method('buildViolation')->with(
            'validation.identifier_generator.attribute_should_have_type',
            ['{{ code }}' => 'sku', '{{ type }}' => 'pim_catalog_text', '{{ expected }}' => 'pim_catalog_identifier']
        );
        $this->sut->validate('sku', new AttributeShouldHaveType(['type' => 'pim_catalog_identifier']));
    }

    public function test_it_should_be_valid_when_target_attribute_is_an_identifier(): void
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
        $this->sut->validate('sku', new AttributeShouldHaveType(['type' => 'pim_catalog_identifier']));
    }
}
