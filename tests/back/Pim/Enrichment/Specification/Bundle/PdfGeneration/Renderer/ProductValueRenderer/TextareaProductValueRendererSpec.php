<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use PhpSpec\ObjectBehavior;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class TextareaProductValueRendererSpec extends ObjectBehavior
{
    function it_supports_textarea_attributes()
    {
        $this->supportsAttributeType(AttributeTypes::TEXTAREA)->shouldReturn(true);
        $this->supportsAttributeType(AttributeTypes::BOOLEAN)->shouldReturn(false);
    }

    function it_strips_unsafe_tags_from_wysiwyg_value(ValueInterface $value, LoaderInterface $loader)
    {
        $environment = new Environment($loader->getWrappedObject());
        $attribute = new Attribute();
        $attribute->setWysiwygEnabled(true);

        $value
            ->getData()
            ->shouldBeCalled()
            ->willReturn('<p>a text</p>');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('<p>a text</p>');
    }

    function it_removes_script_tags_from_wysiwyg_value(ValueInterface $value, LoaderInterface $loader)
    {
        $environment = new Environment($loader->getWrappedObject());
        $attribute = new Attribute();
        $attribute->setWysiwygEnabled(true);

        $value
            ->getData()
            ->shouldBeCalled()
            ->willReturn('<p>text</p><script>alert("xss")</script>');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('<p>text</p>alert("xss")');
    }

    function it_escapes_value(ValueInterface $value, LoaderInterface $loader)
    {
        $environment = new Environment($loader->getWrappedObject());
        $attribute = new Attribute();
        $attribute->setWysiwygEnabled(false);

        $value
            ->__toString()
            ->shouldBeCalled()
            ->willReturn('<div>a text</div>');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('&lt;div&gt;a text&lt;/div&gt;');
    }
}
