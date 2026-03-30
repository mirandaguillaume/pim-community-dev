<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationUserIntentCollectionTest extends TestCase
{
    private QuantifiedAssociationUserIntentCollection $sut;

    protected function setUp(): void
    {
        $this->sut = new QuantifiedAssociationUserIntentCollection([new AssociateQuantifiedProducts('X_SELL', [new QuantifiedEntity('foo', 5)])]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(QuantifiedAssociationUserIntentCollection::class, $this->sut);
        $this->assertInstanceOf(UserIntent::class, $this->sut);
    }

    public function test_it_returns_the_association_user_intents(): void
    {
        $userIntent = new AssociateQuantifiedProducts('X_SELL', [new QuantifiedEntity('foo', 5)]);
        $this->sut = new QuantifiedAssociationUserIntentCollection([$userIntent]);
        $this->assertSame([$userIntent], $this->sut->quantifiedAssociationUserIntents());
    }

    public function test_it_cannot_be_instantiated_with_other_intent_than_association_intent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QuantifiedAssociationUserIntentCollection([new SetTextValue('code', null, null, 'value')]);
    }
}
