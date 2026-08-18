<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch\Sorter\Attribute;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Attribute\BaseAttributeSorter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseAttributeSorterTest extends TestCase
{
    private AttributeValidatorHelper|MockObject $attrValidatorHelper;
    private SearchQueryBuilder|MockObject $searchQueryBuilder;
    private BaseAttributeSorter $sut;

    protected function setUp(): void
    {
        $this->attrValidatorHelper = $this->createMock(AttributeValidatorHelper::class);
        $this->searchQueryBuilder = $this->createMock(SearchQueryBuilder::class);
        $this->sut = new BaseAttributeSorter($this->attrValidatorHelper);
        $this->sut->setQueryBuilder($this->searchQueryBuilder);
    }

    public function test_it_throws_when_no_search_query_builder_was_set(): void
    {
        $sut = new BaseAttributeSorter($this->attrValidatorHelper);

        $this->expectException(\LogicException::class);

        $sut->addAttributeSorter($this->attribute(), 'ASC');
    }

    public function test_it_sorts_ascending_on_the_attribute_path_without_any_suffix(): void
    {
        $this->searchQueryBuilder->expects($this->once())
            ->method('addSort')
            ->with([
                'values.a_code-text.<all_channels>.<all_locales>' => [
                    'order' => 'ASC',
                    'missing' => '_last',
                    'unmapped_type' => 'long',
                ],
            ]);

        $this->sut->addAttributeSorter($this->attribute(), 'ASC');
    }

    public function test_it_sorts_descending_on_the_attribute_path_without_any_suffix(): void
    {
        $this->searchQueryBuilder->expects($this->once())
            ->method('addSort')
            ->with([
                'values.a_code-text.<all_channels>.<all_locales>' => [
                    'order' => 'DESC',
                    'missing' => '_last',
                    'unmapped_type' => 'long',
                ],
            ]);

        $this->sut->addAttributeSorter($this->attribute(), 'DESC');
    }

    public function test_it_throws_on_an_unsupported_direction(): void
    {
        $this->expectException(InvalidDirectionException::class);

        $this->sut->addAttributeSorter($this->attribute(), 'sideways');
    }

    private function attribute(): AttributeInterface|MockObject
    {
        $attribute = $this->createMock(AttributeInterface::class);
        $attribute->method('getCode')->willReturn('a_code');
        $attribute->method('getBackendType')->willReturn('text');
        $attribute->method('isLocalizable')->willReturn(false);
        $attribute->method('isScopable')->willReturn(false);

        return $attribute;
    }
}
