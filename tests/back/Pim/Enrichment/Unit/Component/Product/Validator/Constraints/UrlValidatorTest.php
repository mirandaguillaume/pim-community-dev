<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Url;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UrlValidator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Constraints\UrlValidator as BaseUrlValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UrlValidatorTest extends TestCase
{
    private ExecutionContextInterface|MockObject $context;
    private UrlValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new UrlValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UrlValidator::class, $this->sut);
    }

    public function test_it_is_a_validator_constraint(): void
    {
        $this->assertInstanceOf(BaseUrlValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidator::class, $this->sut);
    }

    public function test_it_allows_null_value(): void
    {
        $constraint = new Url(['attributeCode' => 'a_code']);
        $this->context->method('getViolations')->willReturn(new ConstraintViolationList());
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(null, $constraint);
    }

    public function test_it_allows_empty_value(): void
    {
        $constraint = new Url(['attributeCode' => 'a_code']);
        $this->context->method('getViolations')->willReturn(new ConstraintViolationList());
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('', $constraint);
    }

    public function test_it_does_not_validate_a_bad_url(): void
    {
        $constraintViolation = $this->createMock(ConstraintViolation::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $badUrl = 'htp://bad.url';
        $constraint = new Url(['attributeCode' => 'a_code']);
        $this->context->method('buildViolation')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->willReturnSelf();
        $constraintViolationBuilder->method('setCode')->willReturnSelf();
        $constraintViolationBuilder->method('setInvalidValue')->willReturnSelf();
        $constraintViolationBuilder->expects($this->atLeastOnce())->method('addViolation');
        $constraintViolationList = new ConstraintViolationList([$constraintViolation]);
        $this->context->method('getViolations')->willReturn($constraintViolationList);
        $constraintViolation->method('getCode')->willReturn(Url::INVALID_URL_ERROR);
        $this->sut->validate($badUrl, $constraint);
    }

    public function test_it_throws_an_exception_if_the_constraint_is_not_an_url(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->sut->validate('value', new IsString());
    }

    public function test_it_validates_a_good_url(): void
    {
        $goodUrl = 'https://www.akeneo.com';
        $constraint = new Url(['attributeCode' => 'a_code']);
        $this->context->method('getViolations')->willReturn(new ConstraintViolationList());
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($goodUrl, $constraint);
    }
}
