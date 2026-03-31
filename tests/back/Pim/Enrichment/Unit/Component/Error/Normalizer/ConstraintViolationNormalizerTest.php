<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Error\Normalizer;

use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderRegistry;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Normalizer\ConstraintViolationNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConstraintViolationNormalizerTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $attributeRepository;
    private DocumentationBuilderRegistry|MockObject $documentationBuilderRegistry;
    private ConstraintViolationNormalizer $sut;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->documentationBuilderRegistry = $this->createMock(DocumentationBuilderRegistry::class);
        $this->sut = new ConstraintViolationNormalizer($this->attributeRepository, $this->documentationBuilderRegistry);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConstraintViolationNormalizer::class, $this->sut);
    }

    public function test_it_supports_constraint_violation(): void
    {
        $constraintViolation = $this->createMock(ConstraintViolationInterface::class);

        $this->assertSame(true, $this->sut->supportsNormalization($constraintViolation));
    }

    public function test_it_normalizes_a_constraint_violation(): void
    {
        $constraintViolation = new ConstraintViolation(
                    'Property "clothing_size" expects a valid code. The option "z" does not exist',
                    'Property "%attribute_code%" expects a valid code. The option "%invalid_option%" does not exist',
                    [
                        '%attribute_code%' => 'clothing_size',
                        '%invalid_option%' => 'z'
                    ],
                    '',
                    'values',
                    ''
                );
        $this->documentationBuilderRegistry->method('getDocumentation')->willReturn(new DocumentationCollection([]));
        $this->assertSame([
                        'property' => 'values',
                        'message' => 'Property "clothing_size" expects a valid code. The option "z" does not exist',
                        'type' => 'violation_error',
                        'message_template' => 'Property "%attribute_code%" expects a valid code. The option "%invalid_option%" does not exist',
                        'message_parameters' => [
                            '%attribute_code%' => 'clothing_size',
                            '%invalid_option%' => 'z'
                        ],
                        'documentation' => []
                    ], $this->sut->normalize($constraintViolation));
    }

    public function test_it_normalizes_a_product_without_family(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $constraintViolation = new ConstraintViolation('', '', [], '', '', '');
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $product->method('getIdentifier')->willReturn('product_identifier');
        $product->method('getFamily')->willReturn(null);
        $product->method('getLabel')->willReturn('Akeneo T-Shirt black and purple with short sleeve');
        $this->assertSame([
                    'property' => '',
                    'message' => '',
                    'type' => 'violation_error',
                    'message_template' => '',
                    'message_parameters' => [],
                    'product' => [
                        'uuid' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                        'identifier' => 'product_identifier',
                        'label' => 'Akeneo T-Shirt black and purple with short sleeve',
                        'family' => null,
                    ]
                ], $this->sut->normalize($constraintViolation, 'json', ['product' => $product]));
    }

    public function test_it_normalizes_a_product(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $family = $this->createMock(FamilyInterface::class);

        $constraintViolation = new ConstraintViolation('', '', [], '', '', '');
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $product->method('getIdentifier')->willReturn('product_identifier');
        $product->method('getFamily')->willReturn($family);
        $family->method('getCode')->willReturn('tshirts');
        $product->method('getLabel')->willReturn('Akeneo T-Shirt black and purple with short sleeve');
        $this->assertSame([
                    'property' => '',
                    'message' => '',
                    'type' => 'violation_error',
                    'message_template' => '',
                    'message_parameters' => [],
                    'product' => [
                        'uuid' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                        'identifier' => 'product_identifier',
                        'label' => 'Akeneo T-Shirt black and purple with short sleeve',
                        'family' => 'tshirts',
                    ]
                ], $this->sut->normalize($constraintViolation, 'json', ['product' => $product]));
    }
}
