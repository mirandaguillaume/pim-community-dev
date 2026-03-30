<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales\InMemoryLocales;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryLocalesTest extends TestCase
{
    private InMemoryLocales $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryLocales([
            'en_US' => 58,
            'fr_FR' => 90,
        ]);
    }

    public function test_it_gets_a_locale_id_from_its_id(): void
    {
        $this->assertSame(90, $this->sut->getIdByCode('fr_FR'));
    }

    public function test_it_gets_a_locale_code_from_its_id(): void
    {
        $this->assertSame('fr_FR', $this->sut->getCodeById(90));
    }

    public function test_it_returns_null_if_the_locale_does_not_exist(): void
    {
        $this->assertNull($this->sut->getIdByCode('fo_BA'));
        $this->assertNull($this->sut->getCodeById(999));
    }
}
