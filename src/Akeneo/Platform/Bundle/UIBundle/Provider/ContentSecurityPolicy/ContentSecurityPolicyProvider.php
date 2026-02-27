<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy;

use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;

final readonly class ContentSecurityPolicyProvider
{
    /**
     * @param \Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface[] $contentSecurityPolicyProviders
     */
    public function __construct(private iterable $contentSecurityPolicyProviders) {}

    public function getPolicy(): string
    {
        $policies = [];
        foreach ($this->contentSecurityPolicyProviders as $contentSecurityPolicyProvider) {
            $policies = array_merge_recursive($policies, $contentSecurityPolicyProvider->getContentSecurityPolicy());
        }

        $policiesAsString = [];
        foreach ($policies as $directive => $policy) {
            $policiesAsString[] = $directive . ' ' . join(' ', array_unique($policy));
        }

        return join('; ', $policiesAsString);
    }
}
