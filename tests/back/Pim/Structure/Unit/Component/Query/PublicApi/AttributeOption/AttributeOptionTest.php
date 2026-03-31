<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Query\PublicApi\AttributeOption;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\AttributeOption;
use PHPUnit\Framework\TestCase;

class AttributeOptionTest extends TestCase
{
    private AttributeOption $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeOption('an_attribute_option_code',
            [
                'en_US' => 'An attribute option',
                'fr_FR' => 'Une option',
            ]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AttributeOption::class, $this->sut);
    }

    public function test_it_returns_the_code(): void
    {
        $this->assertSame('an_attribute_option_code', $this->sut->getCode());
    }

    public function test_it_returns_the_labels(): void
    {
        $this->assertSame([
                    'en_US' => 'An attribute option',
                    'fr_FR' => 'Une option',
                ], $this->sut->getLabels());
    }

    public function test_it_normalizes_itself(): void
    {
        $this->assertSame([
                    'code' => 'an_attribute_option_code',
                    'labels' => [
                        'en_US' => 'An attribute option',
                        'fr_FR' => 'Une option',
                    ]
                ], $this->sut->normalize());
    }
}
