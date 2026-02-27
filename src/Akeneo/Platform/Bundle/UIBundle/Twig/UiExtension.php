<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Akeneo\Platform\Bundle\UIBundle\Twig\Parser\PlaceholderTokenParser;
use Twig\Extension\AbstractExtension;

class UiExtension extends AbstractExtension
{
    public function __construct(protected $placeholders, protected $wrapClassName)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenParsers()
    {
        return [
            new PlaceholderTokenParser($this->placeholders, $this->wrapClassName),
        ];
    }
}
