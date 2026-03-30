<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Error\Normalizer;

use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderRegistry;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\Normalizer\ProductDomainErrorNormalizer;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductDomainErrorNormalizerTest extends TestCase
{
    private DocumentationBuilderRegistry|MockObject $documentationBuilderRegistry;
    private ProductDomainErrorNormalizer $sut;

    protected function setUp(): void
    {
        $this->documentationBuilderRegistry = $this->createMock(DocumentationBuilderRegistry::class);
        $this->sut = new ProductDomainErrorNormalizer($this->documentationBuilderRegistry);
        $this->documentationBuilderRegistry->method('getDocumentation')->with($this->anything())->willReturn(null);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductDomainErrorNormalizer::class, $this->sut);
    }

    public function test_it_supports_a_domain_error(): void
    {
        $error = $this->createMock(DomainErrorInterface::class);

        $this->assertSame(true, $this->sut->supportsNormalization($error));
    }

    public function test_it_normalizes_a_domain_error(): void
    {
        $error = new class ('Some error message') extends \Exception implements DomainErrorInterface
                {
                }
        ;
        $this->assertSame([
                    'type' => 'domain_error',
                    'message' => 'Some error message',
                ], $this->sut->normalize($error, 'json', []));
    }

    public function test_it_normalizes_a_templated_error_message(): void
    {
        $error = new class ('Some error message') extends \Exception implements DomainErrorInterface, TemplatedErrorMessageInterface
                {
                    public function getTemplatedErrorMessage(): TemplatedErrorMessage
                    {
                        return new TemplatedErrorMessage('My message template with {param}.', ['param' => 'a param']);
                    }
                }
        ;
        $this->assertSame([
                    'type' => 'domain_error',
                    'message' => 'Some error message',
                    'message_template' => 'My message template with {param}.',
                    'message_parameters' => ['param' => 'a param']
                ], $this->sut->normalize($error, 'json', []));
    }

    public function test_it_normalizes_the_product_without_family(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $error = new class ('Some error message') extends \Exception implements DomainErrorInterface
                {
                }
        ;
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $product->method('getIdentifier')->willReturn('product_identifier');
        $product->method('getFamily')->willReturn(null);
        $product->method('getLabel')->willReturn('Akeneo T-Shirt black and purple with short sleeve');
        $this->assertSame([
                    'type' => 'domain_error',
                    'message' => 'Some error message',
                    'product' => [
                        'uuid' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                        'identifier' => 'product_identifier',
                        'label' => 'Akeneo T-Shirt black and purple with short sleeve',
                        'family' => null,
                    ]
                ], $this->sut->normalize($error, 'json', ['product' => $product]));
    }

    public function test_it_normalizes_a_documented_error(): void
    {
        $error = new class ('Some error message') extends \Exception implements DomainErrorInterface
                {
                }
        ;
        $this->documentationBuilderRegistry->method('getDocumentation')->with($error)->willReturn(new DocumentationCollection([]));
        $this->assertSame([
                    'type' => 'domain_error',
                    'message' => 'Some error message',
                    'documentation' => []
                ], $this->sut->normalize($error, 'json', []));
    }

    public function test_it_normalizes_the_product_information(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $family = $this->createMock(FamilyInterface::class);

        $error = new class ('Some error message') extends \Exception implements DomainErrorInterface
                {
                }
        ;
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $product->method('getIdentifier')->willReturn('product_identifier');
        $product->method('getFamily')->willReturn($family);
        $family->method('getCode')->willReturn('tshirts');
        $product->method('getLabel')->willReturn('Akeneo T-Shirt black and purple with short sleeve');
        $this->assertSame([
                    'type' => 'domain_error',
                    'message' => 'Some error message',
                    'product' => [
                        'uuid' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                        'identifier' => 'product_identifier',
                        'label' => 'Akeneo T-Shirt black and purple with short sleeve',
                        'family' => 'tshirts',
                    ]
                ], $this->sut->normalize($error, 'json', ['product' => $product]));
    }
}
