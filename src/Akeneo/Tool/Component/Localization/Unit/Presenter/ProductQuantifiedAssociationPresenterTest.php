<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization\Presenter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindIdentifier;
use Akeneo\Tool\Component\Localization\Presenter\ProductQuantifiedAssociationPresenter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQuantifiedAssociationPresenterTest extends TestCase
{
    private FindIdentifier|MockObject $findIdentifier;
    private AssociationColumnsResolver|MockObject $associationColumnsResolver;
    private ProductQuantifiedAssociationPresenter $sut;

    protected function setUp(): void
    {
        $this->findIdentifier = $this->createMock(FindIdentifier::class);
        $this->associationColumnsResolver = $this->createMock(AssociationColumnsResolver::class);
        $this->sut = new ProductQuantifiedAssociationPresenter($this->findIdentifier, $this->associationColumnsResolver);
        $this->associationColumnsResolver->method('resolveQuantifiedIdentifierAssociationColumns')->willReturn([
        'QUANTIFIED-products',
        'QUANTIFIED-product_models',
        'PACKAGE-products',
        'PACKAGE-product_models',
        ]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductQuantifiedAssociationPresenter::class, $this->sut);
    }

    public function test_it_only_supports_product_association(): void
    {
        $this->assertSame(true, $this->sut->supports('QUANTIFIED-products'));
        $this->assertSame(false, $this->sut->supports('QUANTIFIED-product_models'));
        $this->assertSame(true, $this->sut->supports('PACKAGE-products'));
        $this->assertSame(false, $this->sut->supports('X_SELL-products'));
        $this->assertSame(false, $this->sut->supports('name-en_US'));
    }

    public function test_it_presents_identifier_when_product_uuid_is_passed(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $this->findIdentifier->expects($this->once())->method('fromUuids')->with([$uuid])->willReturn([$uuid => 'my-identifier']);
        $value = implode(',', [$uuid]);
        $this->assertSame('my-identifier', $this->sut->present($value));
    }

    public function test_it_presents_uuid_when_product_uuid_is_passed_and_product_has_no_identifier(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $this->findIdentifier->expects($this->once())->method('fromUuids')->with([$uuid])->willReturn([$uuid => null]);
        $value = implode(',', [$uuid]);
        $this->assertSame(sprintf('[%s]', $uuid), $this->sut->present($value));
    }

    public function test_it_presents_identifiers_when_products_uuids_are_passed(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();
        $this->findIdentifier->expects($this->once())->method('fromUuids')->with([$uuid, $uuid2])->willReturn([$uuid => 'my-identifier', $uuid2 => 'my-identifier-2']);
        $value = implode(',', [$uuid, $uuid2]);
        $this->assertSame('my-identifier,my-identifier-2', $this->sut->present($value));
    }

    public function test_it_presents_input_string_when_is_invalid(): void
    {
        $this->findIdentifier->expects($this->once())->method('fromUuids')->with([])->willReturn([]);
        $value = 'invalid_uuid';
        $this->assertSame('invalid_uuid', $this->sut->present($value));
    }

    public function test_it_presents_uuid_when_product_is_not_found(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $this->findIdentifier->expects($this->once())->method('fromUuids')->with([$uuid])->willReturn([]);
        $this->assertSame(sprintf('[%s]', $uuid), $this->sut->present($uuid));
    }

    public function test_it_presents_mixed_result_when_several_products_are_finded(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();
        $uuid3 = Uuid::uuid4()->toString();
        $uuid4 = 'invalid_uuid';
        $this->findIdentifier->expects($this->once())->method('fromUuids')->with([$uuid, $uuid2, $uuid3])->willReturn([$uuid => 'my-identifier', $uuid2 => null]);
        $value = implode(',', [$uuid, $uuid2, $uuid3, $uuid4]);
        $this->assertSame(sprintf('%s,[%s],[%s],%s', 'my-identifier', $uuid2, $uuid3, $uuid4), $this->sut->present($value));
    }
}
