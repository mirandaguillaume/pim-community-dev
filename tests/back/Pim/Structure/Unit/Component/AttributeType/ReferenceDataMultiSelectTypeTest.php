<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\ReferenceDataMultiSelectType;
use Akeneo\Pim\Structure\Component\AttributeTypeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataMultiSelectTypeTest extends TestCase
{
    private ReferenceDataMultiSelectType $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataMultiSelectType('options');
    }

    public function test_it_is_an_attribute_type(): void
    {
        $this->assertInstanceOf(AttributeTypeInterface::class, $this->sut);
    }

    public function test_its_name_is_pim_reference_data_multiselect(): void
    {
        $this->assertSame('pim_reference_data_multiselect', $this->sut->getName());
    }

    public function test_it_returns_the_configured_backend_type(): void
    {
        $this->assertSame('options', $this->sut->getBackendType());
    }

    public function test_it_is_not_unique(): void
    {
        $this->assertFalse($this->sut->isUnique());
    }
}
