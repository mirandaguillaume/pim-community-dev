<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Error\Documentation;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\MessageParameterInterface;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\MessageParameterTypes;
use PHPUnit\Framework\TestCase;

class HrefMessageParameterTest extends TestCase
{
    private HrefMessageParameter $sut;

    protected function setUp(): void
    {
        $this->sut = new HrefMessageParameter('What is an attribute?',
            'https://help.akeneo.com/what-is-an-attribute.html');
    }

    public function test_it_is_a_href_message_parameter(): void
    {
        $this->assertInstanceOf(HrefMessageParameter::class, $this->sut);
        $this->assertInstanceOf(MessageParameterInterface::class, $this->sut);
    }

    public function test_it_normalizes_information(): void
    {
        $this->assertSame([
                    'type' => MessageParameterTypes::HREF,
                    'href' => 'https://help.akeneo.com/what-is-an-attribute.html',
                    'title' => 'What is an attribute?',
                ], $this->sut->normalize());
    }

    public function test_it_validates_the_href(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage(sprintf(
                            'Class "%s" need an URL as href argument, "%s" given.',
                            HrefMessageParameter::class,
                            'help.akeneo.com/what-is-an-attribute.html'
                        ));
        new HrefMessageParameter('What is an attribute?',
                    'help.akeneo.com/what-is-an-attribute.html');
    }
}
