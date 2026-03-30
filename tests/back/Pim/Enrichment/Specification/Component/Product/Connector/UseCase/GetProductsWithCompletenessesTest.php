<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithCompletenessesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class GetProductsWithCompletenessesTest extends TestCase
{
    private GetProductCompletenesses|MockObject $getProductCompletenesses;
    private GetProductsWithCompletenesses $sut;

    protected function setUp(): void
    {
        $this->getProductCompletenesses = $this->createMock(GetProductCompletenesses::class);
        $this->sut = new GetProductsWithCompletenesses($this->getProductCompletenesses);
    }

    public function test_it_is_a_get_product_with_completenesses(): void
    {
        $this->assertInstanceOf(GetProductsWithCompletenesses::class, $this->sut);
        $this->assertInstanceOf(GetProductsWithCompletenessesInterface::class, $this->sut);
    }

    public function test_it_builds_a_product_with_completenesses(): void
    {
        $completenesses = [
                    new ProductCompleteness('ecommerce', 'en_US', 10, 5),
                    new ProductCompleteness('ecommerce', 'fr_FR', 10, 1),
                    new ProductCompleteness('print', 'en_US', 4, 0),
                ];
        $completenessCollection = new ProductCompletenessCollection(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'), $completenesses);
        $connectorProduct = $this->getConnectorProduct(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $this->getProductCompletenesses->method('fromProductUuid')->with(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'))->willReturn($completenessCollection);
        $productWithCompleteness = $this->fromConnectorProduct($connectorProduct);
        $productWithCompleteness->completenesses()->shouldReturn($completenessCollection);
    }

    public function test_it_builds_a_product_list_with_completenesses(): void
    {
        $completenesses = [
                    new ProductCompleteness('ecommerce', 'en_US', 10, 5),
                    new ProductCompleteness('ecommerce', 'fr_FR', 10, 1),
                ];
        $completenessesList = [
                    '54162e35-ff81-48f1-96d5-5febd3f00fd5' => new ProductCompletenessCollection(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'), $completenesses),
                    'd9f573cc-8905-4949-8151-baf9d5328f26' => new ProductCompletenessCollection(Uuid::fromString('d9f573cc-8905-4949-8151-baf9d5328f26'), $completenesses),
                    'fdf6f091-3f75-418f-98af-8c19db8b0000' => new ProductCompletenessCollection(Uuid::fromString('fdf6f091-3f75-418f-98af-8c19db8b0000'), []),
                ];
        $connectorProductList = new ConnectorProductList(
                    2,
                    [
                        $this->getConnectorProduct(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5')),
                        $this->getConnectorProduct(Uuid::fromString('d9f573cc-8905-4949-8151-baf9d5328f26')),
                        $this->getConnectorProduct(Uuid::fromString('fdf6f091-3f75-418f-98af-8c19db8b0000'), false),
                    ]
                );
        $this->getProductCompletenesses->method('fromProductUuids')->with([
                        Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
                        Uuid::fromString('d9f573cc-8905-4949-8151-baf9d5328f26'),
                        Uuid::fromString('fdf6f091-3f75-418f-98af-8c19db8b0000')
                    ], 'ecommerce', ['fr_FR', 'en_US'])->willReturn($completenessesList);
        $productListWithCompleteness = $this->fromConnectorProductList($connectorProductList, 'ecommerce', ['fr_FR', 'en_US']);
        foreach ($productListWithCompleteness as $productWithCompleteness) {
                    $productWithCompleteness->completeness()->shouldReturn($completenessesList[$productWithCompleteness->id()]);
                }
    }

    private function getConnectorProduct(UuidInterface $uuid, bool $withFamily = true): ConnectorProduct
    {
            return new ConnectorProduct(
                $uuid,
                'identifier',
                new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
                true,
                $withFamily ? 'clothes' : null,
                [],
                [],
                null,
                [],
                [],
                [],
                new ReadValueCollection(),
                null,
                null
            );
        }
}
