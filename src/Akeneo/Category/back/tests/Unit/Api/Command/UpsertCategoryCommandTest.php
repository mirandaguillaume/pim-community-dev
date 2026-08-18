<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Api\Command;

use Akeneo\Category\Api\Command\UpsertCategoryCommand;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertCategoryCommandTest extends TestCase
{
    public function testItIsInitializable(): void
    {
        $command = new UpsertCategoryCommand('a_category_code');

        $this->assertInstanceOf(UpsertCategoryCommand::class, $command);
    }

    public function testItReturnsTheCategoryCode(): void
    {
        $command = new UpsertCategoryCommand('a_category_code');

        $this->assertSame('a_category_code', $command->categoryCode());
    }

    public function testItDefaultsToNoUserIntents(): void
    {
        $command = new UpsertCategoryCommand('a_category_code');

        $this->assertSame([], $command->userIntents());
    }

    public function testItReturnsTheGivenUserIntents(): void
    {
        $setLabel = new SetLabel('en_US', 'A label');
        $command = new UpsertCategoryCommand('a_category_code', [$setLabel]);

        $this->assertSame([$setLabel], $command->userIntents());
    }

    public function testItRejectsAUserIntentThatDoesNotImplementTheInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UpsertCategoryCommand('a_category_code', ['not_a_user_intent']);
    }

    public function testCreateFiltersOutAnythingThatIsNotAUserIntent(): void
    {
        $setLabel = new SetLabel('en_US', 'A label');
        $command = UpsertCategoryCommand::create('a_category_code', [$setLabel, 'not_a_user_intent', 42]);

        $this->assertSame([$setLabel], $command->userIntents());
    }

    public function testCreateReturnsTheCategoryCode(): void
    {
        $command = UpsertCategoryCommand::create('a_category_code', []);

        $this->assertSame('a_category_code', $command->categoryCode());
    }
}
