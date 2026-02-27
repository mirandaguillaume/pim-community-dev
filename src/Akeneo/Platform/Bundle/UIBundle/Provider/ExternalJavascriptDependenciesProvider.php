<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Provider;

final readonly class ExternalJavascriptDependenciesProvider
{
    public function __construct(private iterable $externalDependenciesProviders)
    {
    }

    public function getScripts(): array
    {
        $dependencies = [];

        foreach ($this->externalDependenciesProviders as $externalDependenciesProvider) {
            $dependencies[] = $externalDependenciesProvider->getScript();
        }

        return $dependencies;
    }
}
