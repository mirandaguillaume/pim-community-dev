<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Api\Command\UserIntents;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetLabelTest extends TestCase
{
    private SetLabel $sut;

    protected function setUp(): void
    {
        $this->sut = new SetLabel('en_US', 'The label');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetLabel::class, $this->sut);
        $this->assertInstanceOf(UserIntent::class, $this->sut);
    }

    public function test_it_returns_the_locale_code(): void
    {
        $this->assertSame('en_US', $this->sut->localeCode());
    }

    public function test_it_returns_the_label_value(): void
    {
        $this->assertSame('The label', $this->sut->label());
    }

    public function test_it_can_set_label_null(): void
    {
        $this->sut = new SetLabel('en_US', null);
        $this->assertNull($this->sut->label());
    }

    public function test_it_set_label_to_null_when_empty(): void
    {
        $this->sut = new SetLabel('en_US', '');
        $this->assertNull($this->sut->label());
    }
}
