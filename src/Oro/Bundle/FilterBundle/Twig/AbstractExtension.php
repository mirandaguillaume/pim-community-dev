<?php

namespace Oro\Bundle\FilterBundle\Twig;

abstract class AbstractExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * Extension name
     */
    public const NAME = 'oro_filter_abstract';

    /**
     * @var array
     */
    protected $defaultFunctionOptions = [
        'is_safe'           => ['html'],
        'needs_environment' => true
    ];

    /**
     * @param string $templateName
     */
    public function __construct(protected $templateName)
    {
    }
}
