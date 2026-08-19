<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\CountItemsWithAttributeValueAction;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountItemsWithAttributeValueActionTest extends TestCase
{
    private CountProductsWithRemovedAttributeInterface|MockObject $countProductsWithRemovedAttribute;
    private CountProductModelsWithRemovedAttributeInterface|MockObject $countProductModelsWithRemovedAttribute;
    private CountItemsWithAttributeValueAction $sut;

    protected function setUp(): void
    {
        $this->countProductsWithRemovedAttribute = $this->createMock(CountProductsWithRemovedAttributeInterface::class);
        $this->countProductModelsWithRemovedAttribute = $this->createMock(CountProductModelsWithRemovedAttributeInterface::class);
        $this->sut = new CountItemsWithAttributeValueAction(
            $this->countProductsWithRemovedAttribute,
            $this->countProductModelsWithRemovedAttribute,
        );
    }

    public function test_it_returns_a_bad_request_response_when_no_attribute_code_is_given(): void
    {
        $response = ($this->sut)(Request::create('/'));

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function test_it_returns_the_product_and_product_model_counts_for_the_given_attribute_code(): void
    {
        $this->countProductsWithRemovedAttribute->method('count')->with(['a_code'], false)->willReturn(3);
        $this->countProductModelsWithRemovedAttribute->method('count')->with(['a_code'], false)->willReturn(2);

        $response = ($this->sut)(Request::create('/', 'GET', ['attribute_code' => 'a_code']));

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            \json_encode(['products' => 3, 'product_models' => 2]),
            $response->getContent(),
        );
    }
}
