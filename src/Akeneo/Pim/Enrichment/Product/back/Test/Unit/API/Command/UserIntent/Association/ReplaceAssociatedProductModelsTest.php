<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReplaceAssociatedProductModelsTest extends TestCase
{
    private ReplaceAssociatedProductModels $sut;

    protected function setUp(): void
    {
        $this->sut = new ReplaceAssociatedProductModels('X_SELL', ['code1', 'code2']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ReplaceAssociatedProductModels::class, $this->sut);
        $this->assertInstanceOf(AssociationUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_association_type(): void
    {
        $this->assertSame('X_SELL', $this->sut->associationType());
    }

    public function test_it_returns_the_product_model_codes(): void
    {
        $this->assertSame(['code1', 'code2'], $this->sut->productModelCodes());
    }

    public function test_it_can_only_be_instantiated_with_string_product_model_codes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ReplaceAssociatedProductModels('X_SELL', ['test', 12, false]);
    }

    public function test_it_cannot_be_instantiated_if_one_of_the_product_model_codes_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ReplaceAssociatedProductModels('X_SELL', ['a', '', 'b']);
    }

    public function test_it_cannot_be_instantiated_with_empty_association_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ReplaceAssociatedProductModels('', ['code1', 'code2']);
    }
}
