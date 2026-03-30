<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Normalizer\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Api\Normalizer\Exception\ViolationNormalizer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ViolationNormalizerTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $attributeRepository;
    private ViolationNormalizer $sut;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->sut = new ViolationNormalizer($this->attributeRepository);
    }

    public function test_it_normalizes_an_exception(): void
    {
        $violationCode = new ConstraintViolation('Not Blank', '', [], '', 'code', '');
        $violationName = new ConstraintViolation('Too long', '', [], '', 'name', '');
        $constraintViolation = new ConstraintViolationList([$violationCode, $violationName]);
        $exception = new ViolationHttpException($constraintViolation);
        $this->assertSame([
                    'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'Validation failed.',
                    'errors'  => [
                        ['property' => 'code', 'message' => 'Not Blank'],
                        ['property' => 'name', 'message' => 'Too long'],
                    ],
                ], $this->sut->normalize($exception));
    }

    public function test_it_normalizes_an_exception_with_error_on_product_identifier_when_blank(): void
    {
        $exception = $this->createMock(ViolationHttpException::class);
        $constraintViolations = $this->createMock(ConstraintViolationList::class);
        $violation = $this->createMock(ConstraintViolation::class);
        $product = $this->createMock(EntityWithValuesInterface::class);
        $iterator = $this->createMock(ArrayIterator::class);
        $values = $this->createMock(WriteValueCollection::class);
        $identifier = $this->createMock(ValueInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $constraint = $this->createMock(Constraint::class);

        $attribute->method('getType')->willReturn('pim_catalog_identifier');
        $attribute->method('getCode')->willReturn('identifier');
        $identifier->method('getAttributeCode')->willReturn('identifier');
        $this->attributeRepository->method('findOneByIdentifier')->with('identifier')->willReturn($attribute);
        $product->method('getValues')->willReturn($values);
        $values->method('getByKey')->with('sku')->willReturn($identifier);
        $violation->method('getRoot')->willReturn($product);
        $violation->method('getMessage')->willReturn('Not Blank');
        $violation->method('getPropertyPath')->willReturn('values[sku].text');
        $violation->method('getMessageTemplate')->willReturn('');
        $constraintViolations->method('getIterator')->willReturn($iterator);
        $iterator->expects($this->once())->method('rewind');
        $valueCount = 1;
        // TODO: manual conversion needed — complex .will() callback
        // $iterator->valid()->will(
        //             function () use (&$valueCount) {
        //                 return $valueCount-- > 0;
        //             }
        //         );
        $iterator->method('current')->willReturn($violation);
        $iterator->expects($this->once())->method('next');
        $exception->method('getViolations')->willReturn($constraintViolations);
        $exception->method('getStatusCode')->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);
        $violation->method('getConstraint')->willReturn($constraint);
        $this->assertSame([
                    'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => '',
                    'errors'  => [
                        ['property' => 'identifier', 'message' => 'Not Blank'],
                    ],
                ], $this->sut->normalize($exception));
    }

    public function test_it_normalizes_an_exception_with_error_on_product_identifier_when_too_long(): void
    {
        $exception = $this->createMock(ViolationHttpException::class);
        $constraintViolations = $this->createMock(ConstraintViolationList::class);
        $violationProductValue = $this->createMock(ConstraintViolation::class);
        $product = $this->createMock(EntityWithValuesInterface::class);
        $iterator = $this->createMock(ArrayIterator::class);
        $productValues = $this->createMock(WriteValueCollection::class);
        $sku = $this->createMock(ValueInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $lengthConstraint = $this->createMock(Constraint::class);

        $attribute->method('getType')->willReturn('pim_catalog_identifier');
        $attribute->method('getCode')->willReturn('sku');
        $attribute->method('getMaxCharacters')->willReturn(10);
        $sku->method('getAttributeCode')->willReturn('identifier');
        $sku->method('getLocaleCode')->willReturn(null);
        $sku->method('getScopeCode')->willReturn(null);
        $this->attributeRepository->method('findOneByIdentifier')->with('identifier')->willReturn($attribute);
        $product->method('getValues')->willReturn($productValues);
        $productValues->method('getByKey')->with('sku')->willReturn($sku);
        $violationProductValue->method('getRoot')->willReturn($product);
        $violationProductValue->method('getMessage')->willReturn('Product value sku is too long (10)');
        $violationProductValue->method('getPropertyPath')->willReturn('values[sku].text');
        $violationProductValue->method('getConstraint')->willReturn($lengthConstraint);
        $violationProductValue->method('getMessageTemplate')->willReturn('This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.');
        $constraintViolations->method('getIterator')->willReturn($iterator);
        $iterator->expects($this->once())->method('rewind');
        $valueCount = 1;
        // TODO: manual conversion needed — complex .will() callback
        // $iterator->valid()->will(
        //             function () use (&$valueCount) {
        //                 return $valueCount-- > 0;
        //             }
        //         );
        $iterator->method('current')->willReturn($violationProductValue);
        $iterator->expects($this->once())->method('next');
        $exception->method('getViolations')->willReturn($constraintViolations);
        $exception->method('getStatusCode')->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);
        $violationProductValue->method('getConstraint')->willReturn($lengthConstraint);
        $this->assertSame([
                    'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => '',
                    'errors'  => [
                        [
                            'property' => 'identifier',
                            'message'  => 'Product value sku is too long (10)',
                        ],
                    ],
                ], $this->sut->normalize($exception));
    }

    public function test_it_normalizes_an_exception_with_error_on_product_identifier_when_regexp(): void
    {
        $exception = $this->createMock(ViolationHttpException::class);
        $constraintViolations = $this->createMock(ConstraintViolationList::class);
        $violationIdentifier = $this->createMock(ConstraintViolation::class);
        $violationProductValue = $this->createMock(ConstraintViolation::class);
        $product = $this->createMock(EntityWithValuesInterface::class);
        $iterator = $this->createMock(ArrayIterator::class);
        $productValues = $this->createMock(WriteValueCollection::class);
        $sku = $this->createMock(ValueInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $regexpConstraint = $this->createMock(Constraint::class);

        $attribute->method('getType')->willReturn('pim_catalog_identifier');
        $attribute->method('getCode')->willReturn('sku');
        $attribute->method('getMaxCharacters')->willReturn(10);
        $sku->method('getAttributeCode')->willReturn('sku');
        $sku->method('getLocaleCode')->willReturn(null);
        $sku->method('getScopeCode')->willReturn(null);
        $this->attributeRepository->method('findOneByIdentifier')->with('sku')->willReturn($attribute);
        $product->method('getValues')->willReturn($productValues);
        $productValues->method('getByKey')->with('sku')->willReturn($sku);
        $violationIdentifier->method('getRoot')->willReturn($product);
        $violationIdentifier->method('getMessage')->willReturn('This value is not valid.');
        $violationIdentifier->method('getPropertyPath')->willReturn('identifier');
        $violationIdentifier->method('getConstraint')->willReturn($regexpConstraint);
        $violationIdentifier->method('getMessageTemplate')->willReturn('This value is not valid.');
        $violationProductValue->method('getRoot')->willReturn($product);
        $violationProductValue->method('getMessage')->willReturn('This value is not valid.');
        $violationProductValue->method('getPropertyPath')->willReturn('values[sku].text');
        $violationProductValue->method('getConstraint')->willReturn($regexpConstraint);
        $violationProductValue->method('getMessageTemplate')->willReturn('This value is not valid.');
        $constraintViolations->method('getIterator')->willReturn($iterator);
        $iterator->expects($this->once())->method('rewind');
        $valueCount = 2;
        // TODO: manual conversion needed — complex .will() callback
        // $iterator->valid()->will(
        //             function () use (&$valueCount) {
        //                 return $valueCount-- > 0;
        //             }
        //         );
        $iterator->method('current')->willReturn($violationIdentifier, $violationProductValue);
        $iterator->expects($this->once())->method('next');
        $exception->method('getViolations')->willReturn($constraintViolations);
        $exception->method('getStatusCode')->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);
        $violationIdentifier->method('getConstraint')->willReturn($regexpConstraint);
        $this->assertSame([
                    'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => '',
                    'errors'  => [
                        [
                            'property' => 'identifier',
                            'message'  => 'This value is not valid.',
                        ],
                    ],
                ], $this->sut->normalize($exception));
    }

    public function test_it_normalizes_an_exception_with_error_on_attribute_localizable_and_scopable(): void
    {
        $exception = $this->createMock(ViolationHttpException::class);
        $constraintViolations = $this->createMock(ConstraintViolationList::class);
        $violation = $this->createMock(ConstraintViolation::class);
        $product = $this->createMock(EntityWithValuesInterface::class);
        $iterator = $this->createMock(ArrayIterator::class);
        $productValues = $this->createMock(WriteValueCollection::class);
        $description = $this->createMock(ValueInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $constraint = $this->createMock(Constraint::class);

        $attribute->method('getType')->willReturn('pim_catalog_text');
        $attribute->method('getCode')->willReturn('description');
        $description->method('getAttributeCode')->willReturn('description');
        $description->method('getLocaleCode')->willReturn('en_US');
        $description->method('getScopeCode')->willReturn('ecommerce');
        $this->attributeRepository->method('findOneByIdentifier')->with('description')->willReturn($attribute);
        $product->method('getValues')->willReturn($productValues);
        $productValues->method('getByKey')->with('description-en_US-ecommerce')->willReturn($description);
        $violation->method('getRoot')->willReturn($product);
        $violation->method('getMessage')->willReturn('Not Blank');
        $violation->method('getPropertyPath')->willReturn('values[description-en_US-ecommerce].textarea');
        $violation->method('getMessageTemplate')->willReturn('');
        $constraintViolations->method('getIterator')->willReturn($iterator);
        $iterator->expects($this->once())->method('rewind');
        $valueCount = 1;
        // TODO: manual conversion needed — complex .will() callback
        // $iterator->valid()->will(
        //             function () use (&$valueCount) {
        //                 return $valueCount-- > 0;
        //             }
        //         );
        $iterator->method('current')->willReturn($violation);
        $iterator->expects($this->once())->method('next');
        $exception->method('getViolations')->willReturn($constraintViolations);
        $exception->method('getStatusCode')->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);
        $violation->method('getConstraint')->willReturn($constraint);
        $this->assertSame([
                    'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => '',
                    'errors'  => [
                        [
                            'property'  => 'values',
                            'message'   => 'Not Blank',
                            'attribute' => 'description',
                            'locale'    => 'en_US',
                            'scope'     => 'ecommerce',
                        ],
                    ],
                ], $this->sut->normalize($exception));
    }

    public function test_it_normalizes_an_exception_using_constraint_constraint_payload_instead_of_property_path(): void
    {
        $exception = $this->createMock(ViolationHttpException::class);
        $constraintViolations = $this->createMock(ConstraintViolationList::class);
        $violation = $this->createMock(ConstraintViolation::class);
        $iterator = $this->createMock(ArrayIterator::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $constraint = $this->createMock(Constraint::class);

        $violation->method('getRoot')->willReturn($attribute);
        $violation->method('getMessage')->willReturn('The locale "ab_CD" does not exist.');
        $violation->method('getPropertyPath')->willReturn('translations[0].locale');
        $violation->method('getMessageTemplate')->willReturn('');
        $constraintViolations->method('getIterator')->willReturn($iterator);
        $iterator->expects($this->once())->method('rewind');
        $valueCount = 1;
        // TODO: manual conversion needed — complex .will() callback
        // $iterator->valid()->will(
        //             function () use (&$valueCount) {
        //                 return $valueCount-- > 0;
        //             }
        //         );
        $iterator->method('current')->willReturn($violation);
        $iterator->expects($this->once())->method('next');
        $exception->method('getViolations')->willReturn($constraintViolations);
        $exception->method('getStatusCode')->willReturn(Response::HTTP_UNPROCESSABLE_ENTITY);
        $violation->method('getConstraint')->willReturn($constraint);
        $constraint->payload = ['standardPropertyName' => 'labels'];
        $this->assertSame([
                    'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => '',
                    'errors'  => [
                        [
                            'property' => 'labels',
                            'message'  => 'The locale "ab_CD" does not exist.',
                        ],
                    ],
                ], $this->sut->normalize($exception));
    }
}
