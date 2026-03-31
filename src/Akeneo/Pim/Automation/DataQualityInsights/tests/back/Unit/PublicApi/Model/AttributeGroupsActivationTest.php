<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\PublicApi\Model;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\AttributeGroupsActivation;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use PHPUnit\Framework\TestCase;

class AttributeGroupsActivationTest extends TestCase
{
    private AttributeGroupsActivation $sut;

    protected function setUp(): void
    {
    }

    public function test_it_says_if_an_attribute_group_is_activated(): void
    {
        $rawAttributeGroupsActivation = [
                    'an_attribute_group' => true,
                    'another_attribute_group' => false,
                ];
        $this->sut = new AttributeGroupsActivation($rawAttributeGroupsActivation);
        $this->assertSame(true, $this->sut->isActivated('an_attribute_group'));
        $this->assertSame(false, $this->sut->isActivated('another_attribute_group'));
    }
}
