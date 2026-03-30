<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use PHPUnit\Framework\TestCase;

class SetFamilyTest extends TestCase
{
    private SetFamily $sut;

    protected function setUp(): void
    {
    }

    public function test_it_can_be_constructed_with_family_code(): void
    {
        $this->sut = new SetFamily('accessories');
        $this->assertTrue(is_a(SetFamily::class, SetFamily::class, true));
        $this->assertSame('accessories', $this->sut->familyCode());
    }
}
