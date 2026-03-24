<?php

namespace Akeneo\Tool\Bundle\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\HostRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\PathRequestMatcher;

/**
 * Compiler pass to add rules to the content type negotiator.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContentTypeNegotiatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('pim_api.negotiator.content_type_negotiator')) {
            return;
        }

        $configuration = $container->getParameter('pim_api.configuration');
        $rules = $configuration['content_type_negotiator']['rules'];
        foreach ($rules as $rule) {
            $this->addRule($rule, $container);
        }
    }

    private function addRule(array $rule, ContainerBuilder $container): void
    {
        $matcher = $this->createRequestMatcher(
            $container,
            $rule['path'],
            $rule['host'],
            $rule['methods']
        );

        $container->getDefinition('pim_api.negotiator.content_type_negotiator')
            ->addMethodCall('add', [$matcher, $rule]);
    }

    private function createRequestMatcher(ContainerBuilder $container, ?string $path = null, ?string $host = null, ?array $methods = null): Reference
    {
        $arguments = [$path, $host, $methods];
        $serialized = serialize($arguments);
        $id = 'pim_api.content_type_negotiator.request_matcher.' . md5($serialized) . sha1($serialized);

        if (!$container->hasDefinition($id)) {
            $matchers = [];
            if (null !== $path) {
                $matchers[] = new Definition(PathRequestMatcher::class, [$path]);
            }
            if (null !== $host) {
                $matchers[] = new Definition(HostRequestMatcher::class, [$host]);
            }
            if (null !== $methods) {
                $matchers[] = new Definition(MethodRequestMatcher::class, [$methods]);
            }
            $container->setDefinition($id, new Definition(ChainRequestMatcher::class, [$matchers]));
        }

        return new Reference($id);
    }
}
