<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UuidMappingTest extends TestCase
{
    private UuidMapping $sut;

    protected function setUp(): void
    {
        $this->sut = UuidMapping::createFromMapping([[
            'uuid' => '3f090f5e-3f54-4f34-879c-87779297d130',
            'identifier' => 'product_identifier',
            'id' => 42,
        ]]);
    }

    public function test_it_is_created_from_a_mapping_and_returns_the_id_or_the_identifier(): void
    {
        $uuidAsStr = '3f090f5e-3f54-4f34-879c-87779297d130';
        $uuid = Uuid::fromString($uuidAsStr);
        $identifier = 'product_identifier';
        $this->sut = UuidMapping::createFromMapping([[
                    'uuid' => $uuidAsStr,
                    'identifier' => $identifier,
                    'id' => 42
                ]]);
        $this->assertSame($identifier, $this->sut->getIdentifier($uuid));
        $this->assertSame(true, $this->sut->getUuidFromIdentifier($identifier)->equals($uuid));
        $this->assertSame(true, $this->sut->hasIdentifier($uuid));
        $this->assertSame($uuidAsStr, $this->sut->getUuidFromId(42));
        $this->assertSame(false, $this->sut->hasUuid('nice'));
        $this->assertSame(false, $this->sut->hasIdentifier(Uuid::fromString('52254bba-a2c8-40bb-abe1-195e3970bd93')));
    }

    public function test_it_throws_if_the_product_uuid_is_not_a_real_uuid(): void
    {
        $invalidUuid = 'wrong_uuid';
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createFromMapping([[
                        'uuid' => $invalidUuid,
                        'identifier' => 'product_identifier',
                        'id' => 42
                    ]]);
    }

    public function test_it_throws_if_the_identifier_is_not_an_non_empty_string(): void
    {
        $invalidIdentifier = '';
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createFromMapping([[
                        'uuid' => '3f090f5e-3f54-4f34-879c-87779297d130',
                        'identifier' => $invalidIdentifier,
                        'id' => 42
                    ]]);
    }
}
