<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations\LoadEntitySubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingQueryInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class LoadEntitySubscriberTest extends TestCase
{
    private LoadEntitySubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new LoadEntitySubscriber();
    }

    private function anIdMapping(): IdMapping
    {
            return IdMapping::createFromMapping([1 => 'entity_1', 2 => 'entity_2']);
        }

    private function aUuidMapping(): UuidMapping
    {
            return UuidMapping::createFromMapping([
                ['uuid' => '3f090f5e-3f54-4f34-879c-87779297d130', 'identifier' => 'entity_1', 'id' => 1],
                ['uuid' => '52254bba-a2c8-40bb-abe1-195e3970bd93', 'identifier' => 'entity_2', 'id' => 2],
            ]);
        }
}
