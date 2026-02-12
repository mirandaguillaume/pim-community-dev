<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Akeneo\Platform\Bundle\UIBundle\Provider\ExternalJavascriptDependenciesProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ExternalJavascriptDependenciesExtension extends AbstractExtension
{
    public function __construct(private readonly ExternalJavascriptDependenciesProvider $dependenciesProvider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('external_javascript_dependencies', $this->getExternalJavascriptDependencies(...), ['is_safe' => ['html']]),
        ];
    }

    public function getExternalJavascriptDependencies(): string
    {
        return join("\n", $this->dependenciesProvider->getScripts());
    }
}
