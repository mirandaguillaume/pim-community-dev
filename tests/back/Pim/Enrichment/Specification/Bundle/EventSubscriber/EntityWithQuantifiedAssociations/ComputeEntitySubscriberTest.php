<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations\ComputeEntitySubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelCodesQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingQueryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class ComputeEntitySubscriberTest extends TestCase
{
    private ComputeEntitySubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new ComputeEntitySubscriber();
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
