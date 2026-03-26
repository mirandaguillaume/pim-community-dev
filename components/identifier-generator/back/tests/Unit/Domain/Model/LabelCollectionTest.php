<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelCollectionTest extends TestCase
{
    private LabelCollection $sut;

    protected function setUp(): void {}

    public function test_it_throws_an_exception_when_an_array_key_is_not_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LabelCollection::fromNormalized([
            'en_US' => 'Sugar',
            1 => 'Sucre',
        ]);
    }

    public function test_it_throws_an_exception_when_an_array_key_is_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LabelCollection::fromNormalized([
            'en_US' => 'Sugar',
            '' => 'sucre',
        ]);
    }

    public function test_it_throws_an_exception_when_a_value_is_not_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LabelCollection::fromNormalized([
            'en_US' => 'Sugar',
            'fr_FR' => 12,
        ]);
    }

    public function test_it_filters_empty_labels(): void
    {
        $this->sut = LabelCollection::fromNormalized([
            'en_US' => 'Sugar',
            'fr_FR' => ' ',
        ]);
        $this->assertSame(['en_US' => 'Sugar'], $this->sut->normalize());
    }
}
