<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\Product\ValueNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ValueNormalizerTest extends TestCase
{
    private NormalizerInterface|MockObject $standardNormalizer;
    private ValueNormalizer $sut;

    protected function setUp(): void
    {
        $this->standardNormalizer = $this->createMock(NormalizerInterface::class);
        $this->sut = new ValueNormalizer($this->standardNormalizer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ValueNormalizer::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_datagrid_format_and_product_value(): void
    {
        $value = $this->createMock(ValueInterface::class);

        $this->assertSame(true, $this->sut->supportsNormalization($value, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($value, 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'datagrid'));
    }

    public function test_it_normalizes_a_product_value(): void
    {
        $value = $this->createMock(ValueInterface::class);

        $data =  [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'product_value_data',
                ];
        $this->standardNormalizer->method('normalize')->with($value, 'standard', [])->willReturn($data);
        $this->assertSame($data, $this->sut->normalize($value));
    }
}
