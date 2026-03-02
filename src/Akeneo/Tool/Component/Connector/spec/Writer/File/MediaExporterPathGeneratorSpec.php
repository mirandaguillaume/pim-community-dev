<?php

namespace spec\Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Connector\Writer\File\MediaExporterPathGenerator;
use PhpSpec\ObjectBehavior;

class MediaExporterPathGeneratorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(MediaExporterPathGenerator::class);
    }

    public function it_generates_the_path()
    {
        $value = [
            'locale' => null,
            'scope'  => null,
        ];

        $options = ['identifier' => 'sku001', 'code' => 'picture'];

        $this->generate($value, $options)->shouldReturn('files/sku001/picture/');
    }

    public function it_generates_the_path_when_the_value_is_localisable()
    {
        $value = [
            'locale' => 'fr_FR',
            'scope'  => null,
        ];

        $options = ['identifier' => 'sku001', 'code' => 'picture'];

        $this->generate($value, $options)->shouldReturn('files/sku001/picture/fr_FR/');
    }

    public function it_generates_the_path_when_the_value_is_scopable()
    {
        $value = [
            'locale' => null,
            'scope'  => 'ecommerce',
        ];

        $options = ['identifier' => 'sku001', 'code' => 'picture'];

        $this->generate($value, $options)->shouldReturn('files/sku001/picture/ecommerce/');
    }

    public function it_generates_the_path_when_the_value_is_localisable_and_scopable()
    {
        $value = [
            'locale' => 'fr_FR',
            'scope'  => 'ecommerce',
        ];

        $options = ['identifier' => 'sku001', 'code' => 'picture'];

        $this->generate($value, $options)->shouldReturn('files/sku001/picture/fr_FR/ecommerce/');
    }

    public function it_generates_the_path_when_the_sku_contains_slash()
    {
        $value = [
            'locale' => null,
            'scope'  => null,
        ];

        $options = ['identifier' => 'sku/001', 'code' => 'picture'];

        $this->generate($value, $options)->shouldReturn('files/sku_001/picture/');
    }
}
