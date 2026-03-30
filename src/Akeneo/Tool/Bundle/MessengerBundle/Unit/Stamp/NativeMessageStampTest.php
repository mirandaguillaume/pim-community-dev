<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MessengerBundle\Stamp;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\NativeMessageStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NativeMessageStampTest extends TestCase
{
    private NativeMessageStamp $sut;

    protected function setUp(): void
    {
        $this->sut = new NativeMessageStamp($nativeMessage);
        $nativeMessage = new \stdClass();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(NativeMessageStamp::class, $this->sut);
    }

    public function test_it_is_a_stamp(): void
    {
        $this->assertInstanceOf(StampInterface::class, $this->sut);
    }

    public function test_it_returns_the_native_message(): void
    {
        $nativeMessage = new \stdClass();
        $this->sut = new NativeMessageStamp($nativeMessage);
        $this->assertSame($nativeMessage, $this->sut->getNativeMessage());
    }
}
