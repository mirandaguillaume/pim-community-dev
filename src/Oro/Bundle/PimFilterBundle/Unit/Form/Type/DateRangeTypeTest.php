<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimFilterBundle\Form\Type;

use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Oro\Bundle\PimFilterBundle\Form\Type\DateRangeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class DateRangeTypeTest extends FormIntegrationTestCase
{
    public function test_it_has_a_block_prefix(): void
    {
        $type = new DateRangeType();
        $this->assertSame(DateRangeType::NAME, $type->getBlockPrefix());
    }

    public function test_start_field_has_correct_options(): void
    {
        $form = $this->factory->create(DateRangeType::class);

        $startConfig = $form->get('start')->getConfig();
        $this->assertFalse($startConfig->getOption('required'));
        $this->assertSame('single_text', $startConfig->getOption('widget'));
        $this->assertSame(LocalizerInterface::DEFAULT_DATE_FORMAT, $startConfig->getOption('format'));
        $this->assertFalse($startConfig->getOption('html5'));
        $this->assertSame('UTC', $startConfig->getOption('model_timezone'));
        $this->assertSame('UTC', $startConfig->getOption('view_timezone'));
    }

    public function test_end_field_has_correct_options(): void
    {
        $form = $this->factory->create(DateRangeType::class);

        $endConfig = $form->get('end')->getConfig();
        $this->assertFalse($endConfig->getOption('required'));
        $this->assertSame('single_text', $endConfig->getOption('widget'));
        $this->assertSame(LocalizerInterface::DEFAULT_DATE_FORMAT, $endConfig->getOption('format'));
        $this->assertFalse($endConfig->getOption('html5'));
        $this->assertSame('UTC', $endConfig->getOption('model_timezone'));
        $this->assertSame('UTC', $endConfig->getOption('view_timezone'));
    }

    public function test_field_type_defaults_to_date_type(): void
    {
        $form = $this->factory->create(DateRangeType::class);
        $this->assertInstanceOf(DateType::class, $form->get('start')->getConfig()->getType()->getInnerType());
    }

    public function test_field_options_are_merged_into_start_and_end(): void
    {
        $form = $this->factory->create(DateRangeType::class, null, [
            'field_options' => ['attr' => ['class' => 'custom']],
        ]);

        $this->assertSame(['class' => 'custom'], $form->get('start')->getConfig()->getOption('attr'));
        $this->assertSame(['class' => 'custom'], $form->get('end')->getConfig()->getOption('attr'));
    }

    public function test_start_field_options_override_defaults(): void
    {
        $form = $this->factory->create(DateRangeType::class, null, [
            'start_field_options' => ['attr' => ['class' => 'start-custom']],
        ]);

        $this->assertSame(['class' => 'start-custom'], $form->get('start')->getConfig()->getOption('attr'));
    }

    public function test_end_field_options_override_defaults(): void
    {
        $form = $this->factory->create(DateRangeType::class, null, [
            'end_field_options' => ['attr' => ['class' => 'end-custom']],
        ]);

        $this->assertSame(['class' => 'end-custom'], $form->get('end')->getConfig()->getOption('attr'));
    }
}
