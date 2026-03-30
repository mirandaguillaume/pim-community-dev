<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddToGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertProductCommandTest extends TestCase
{
    private UpsertProductCommand $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_initializable(): void
    {
        $this->assertTrue(is_a(UpsertProductCommand::class, UpsertProductCommand::class, true));
    }

    public function test_it_can_be_constructed_with_value_intents(): void
    {
        $valueUserIntents = [
                    new SetTextValue('name', null, null, 'foo'),
                    new SetNumberValue('name', null, null, '10'),
                    new SetMeasurementValue('power', null, null, '100', 'KILOWATT'),
                    new SetTextareaValue('name', null, null, "<p><span style=\"font-weight: bold;\">title</span></p><p>text</p>"),
                    new ClearValue('name', null, null),
                    new SetBooleanValue('name', null, null, true),
                    new SetDateValue('name', null, null, new \DateTime("2022-03-04T09:35:24+00:00")),
                    new AddMultiSelectValue('name', null, null, ['optionA']),
                    new SetSimpleReferenceEntityValue('name', null, null, 'Akeneo'),
                ];
        $this->sut = UpsertProductCommand::createFromCollection(1, 'identifier1', $valueUserIntents);
        $identifier = ProductIdentifier::fromIdentifier('identifier1');
        $this->assertSame(1, $this->sut->userId());
        $this->assertEquals($identifier, $this->sut->productIdentifierOrUuid());
        $this->assertSame('identifier1', $this->sut->productIdentifierOrUuid()->identifier());
        $this->assertSame($valueUserIntents, $this->sut->valueUserIntents());
    }

    public function test_it_cannot_be_constructed_with_bad_value_user_intent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UpsertProductCommand::createFromCollection(1, '', [new \stdClass()]);
    }

    public function test_it_can_be_constructed_with_field_user_intents(): void
    {
        $familyUserIntent = new SetFamily('accessories');
        $categoryUserIntent = new SetCategories(['master']);
        $this->sut = UpsertProductCommand::createFromCollection(1, 'identifier1', [$familyUserIntent, $categoryUserIntent]);
        $identifier = ProductIdentifier::fromIdentifier('identifier1');
        $this->assertSame(1, $this->sut->userId());
        $this->assertEquals($identifier, $this->sut->productIdentifierOrUuid());
        $this->assertSame('identifier1', $this->sut->productIdentifierOrUuid()->identifier());
        $this->assertSame($familyUserIntent, $this->sut->familyUserIntent());
        $this->assertSame($categoryUserIntent, $this->sut->categoryUserIntent());
        $this->assertSame([], $this->sut->valueUserIntents());
    }

    public function test_it_can_be_constructed_from_a_collection_of_user_intents(): void
    {
        $familyUserIntent = new SetFamily('accessories');
        $categoryUserIntent = new SetCategories(['master']);
        $setTextValue = new SetTextValue('name', null, null, 'foo');
        $setNumberValue = new SetNumberValue('name', null, null, '10');
        $setDateValue = new SetDateValue('name', null, null, new \DateTime("2022-03-04T09:35:24+00:00"));
        $addMultiSelectValue = new AddMultiSelectValue('name', null, null, ['optionA']);
        $setAssetValue = new SetAssetValue('name', null, null, ['packshot1']);
        $setGroupsIntent = new SetGroups(['groupA', 'groupB']);
        $associateQuantifiedProducts = new AssociateQuantifiedProducts('X_SELL', [new QuantifiedEntity('foo', 5)]);
        $this->sut = UpsertProductCommand::createFromCollection(
            10,
            'identifier1',
            [
                        $familyUserIntent,
                        $setTextValue,
                        $setNumberValue,
                        $setDateValue,
                        $addMultiSelectValue,
                        $setAssetValue,
                        $categoryUserIntent,
                        $setGroupsIntent,
                        $associateQuantifiedProducts,
                    ],
        );
        $identifier = ProductIdentifier::fromIdentifier('identifier1');
        $this->assertSame(10, $this->sut->userId());
        $this->assertEquals($identifier, $this->sut->productIdentifierOrUuid());
        $this->assertSame('identifier1', $this->sut->productIdentifierOrUuid()->identifier());
        $this->assertSame($familyUserIntent, $this->sut->familyUserIntent());
        $this->assertSame($categoryUserIntent, $this->sut->categoryUserIntent());
        $this->assertSame($setGroupsIntent, $this->sut->groupUserIntent());
        $this->assertSame([$setTextValue, $setNumberValue, $setDateValue, $addMultiSelectValue, $setAssetValue], $this->sut->valueUserIntents());
        $quantifiedAssociations = $this->quantifiedAssociationUserIntents();
        $quantifiedAssociations->shouldHaveType(QuantifiedAssociationUserIntentCollection::class);
        $quantifiedAssociations->quantifiedAssociationUserIntents()->shouldBe([$associateQuantifiedProducts]);
    }

    public function test_it_cannot_be_constructed_with_multiple_set_enabled_intents(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UpsertProductCommand::createFromCollection(
            1,
            'identifier1',
            [
                        new SetEnabled(true),
                        new SetEnabled(false),
                    ],
        );
    }

    public function test_it_cannot_be_constructed_with_multiple_set_categories_intents(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UpsertProductCommand::createFromCollection(
            1,
            'identifier1',
            [
                        new SetCategories(['foo']),
                        new SetCategories(['bar']),
                    ],
        );
    }

    public function test_it_cannot_be_constructed_with_multiple_groups_intents(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UpsertProductCommand::createFromCollection(
            1,
            'identifier1',
            [
                        new SetGroups(['foo']),
                        new AddToGroups(['bar']),
                    ],
        );
    }

    public function test_it_can_be_constructed_with_product_uuid(): void
    {
        $uuid = Uuid::uuid4();
        $productUuid = ProductUuid::fromUuid($uuid);
        $this->sut = UpsertProductCommand::createWithUuid(
            1,
            $productUuid,
            [],
        );
        $this->assertSame(1, $this->sut->userId());
        $this->assertEquals($productUuid, $this->sut->productIdentifierOrUuid());
        $this->assertSame($uuid, $this->sut->productIdentifierOrUuid()->uuid());
    }

    public function test_it_can_be_constructed_with_identifier(): void
    {
        $productIdentifier = ProductIdentifier::fromIdentifier('identifier1');
        $this->sut = UpsertProductCommand::createWithIdentifier(
            1,
            $productIdentifier,
            [],
        );
        $this->assertSame(1, $this->sut->userId());
        $this->assertEquals($productIdentifier, $this->sut->productIdentifierOrUuid());
        $this->assertSame('identifier1', $this->sut->productIdentifierOrUuid()->identifier());
    }
}
