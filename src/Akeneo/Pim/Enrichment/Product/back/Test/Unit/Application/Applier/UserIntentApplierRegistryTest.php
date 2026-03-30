<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplierRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserIntentApplierRegistryTest extends TestCase
{
    private UserIntentApplier|MockObject $setEnabledApplier;
    private UserIntentApplier|MockObject $setNumberValueApplier;
    private UserIntentApplierRegistry $sut;

    protected function setUp(): void
    {
        $this->setEnabledApplier = $this->createMock(UserIntentApplier::class);
        $this->setNumberValueApplier = $this->createMock(UserIntentApplier::class);
        $this->sut = new UserIntentApplierRegistry([$this->setEnabledApplier, $this->setNumberValueApplier]);
        $this->setEnabledApplier->method('getSupportedUserIntents')->willReturn([SetEnabled::class]);
        $this->setNumberValueApplier->method('getSupportedUserIntents')->willReturn([SetNumberValue::class]);
    }

    public function test_it_returns_the_applier_of_a_user_intent(): void
    {
        $this->assertSame($this->setEnabledApplier, $this->sut->getApplier(new SetEnabled(true)));
        $this->assertSame($this->setEnabledApplier, $this->sut->getApplier(new SetEnabled(false)));
        $this->assertSame($this->setNumberValueApplier, $this->sut->getApplier(new SetNumberValue('attribute', null, null, '1')));
    }

    public function test_it_returns_null_when_no_applier_is_found(): void
    {
        $this->assertNull($this->sut->getApplier(new SetTextValue('description', null, null, 'Lorem Ipsum')));
    }
}
