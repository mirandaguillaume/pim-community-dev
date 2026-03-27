<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Application\Applier\UserIntentApplierRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserIntentApplierRegistryTest extends TestCase
{
    public function test_it_returns_the_applier_of_a_user_intent(): void
    {
        $setLabelApplier = $this->createMock(UserIntentApplier::class);
        $setLabelApplier->method('getSupportedUserIntents')->willReturn([SetLabel::class]);
        $sut = new UserIntentApplierRegistry([$setLabelApplier]);
        $this->assertSame($setLabelApplier, $sut->getApplier(new SetLabel('en_US', 'The label')));
    }
}
