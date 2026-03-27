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

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(SetLabel::class, $this->sut);
        $this->assertInstanceOf(UserIntent::class, $this->sut);
    }

    public function testItReturnsTheLocaleCode(): void
    {
        $this->assertSame('en_US', $this->sut->localeCode());
    }

    public function testItReturnsTheLabelValue(): void
    {
        $this->assertSame('The label', $this->sut->label());
    }

    public function testItCanSetLabelNull(): void
    {
        $this->sut = new SetLabel('en_US', null);
        $this->assertNull($this->sut->label());
    }

    public function testItSetLabelToNullWhenEmpty(): void
    {
        $this->sut = new SetLabel('en_US', '');
        $this->assertNull($this->sut->label());
    }
}
