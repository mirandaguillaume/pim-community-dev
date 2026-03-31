<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationUserIntentCollectionTest extends TestCase
{
    private AssociationUserIntentCollection $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationUserIntentCollection([new AssociateProducts('X_SELL', ['identifier'])]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AssociationUserIntentCollection::class, $this->sut);
        $this->assertInstanceOf(UserIntent::class, $this->sut);
    }

    public function test_it_returns_the_association_user_intents(): void
    {
        $userIntent = new AssociateProducts('X_SELL', ['identifier']);
        $this->sut = new AssociationUserIntentCollection([$userIntent]);
        $this->assertSame([$userIntent], $this->sut->associationUserIntents());
    }

    public function test_it_cannot_be_instantiated_with_other_intent_than_association_intent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AssociationUserIntentCollection([new SetTextValue('code', null, null, 'value')]);
    }
}
