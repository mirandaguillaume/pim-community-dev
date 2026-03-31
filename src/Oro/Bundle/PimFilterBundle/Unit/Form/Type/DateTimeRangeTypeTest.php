<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimFilterBundle\Form\Type;

use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Oro\Bundle\PimFilterBundle\Form\Type\DateRangeType;
use Oro\Bundle\PimFilterBundle\Form\Type\DateTimeRangeType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class DateTimeRangeTypeTest extends FormIntegrationTestCase
{
    public function test_it_has_a_block_prefix(): void
    {
        $type = new DateTimeRangeType();
        $this->assertSame(DateTimeRangeType::NAME, $type->getBlockPrefix());
    }

    public function test_it_extends_date_range_type(): void
    {
        $type = new DateTimeRangeType();
        $this->assertSame(DateRangeType::class, $type->getParent());
    }

    public function test_it_defaults_field_type_to_datetime(): void
    {
        $form = $this->factory->create(DateTimeRangeType::class);
        $this->assertInstanceOf(DateTimeType::class, $form->get('start')->getConfig()->getType()->getInnerType());
    }

    public function test_it_sets_default_field_options(): void
    {
        $form = $this->factory->create(DateTimeRangeType::class);

        $startConfig = $form->get('start')->getConfig();
        $this->assertSame(LocalizerInterface::DEFAULT_DATETIME_FORMAT, $startConfig->getOption('format'));
        $this->assertFalse($startConfig->getOption('html5'));
    }
}
