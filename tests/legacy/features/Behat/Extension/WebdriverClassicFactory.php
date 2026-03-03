<?php

declare(strict_types=1);

namespace Pim\Behat\Extension;

use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Factory for the mink/webdriver-classic-driver (W3C WebDriver protocol).
 *
 * Registered with MinkExtension via WebdriverClassicExtension.
 */
class WebdriverClassicFactory implements DriverFactory
{
    /**
     * {@inheritdoc}
     */
    public function getDriverName()
    {
        return 'webdriver_classic';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsJavascript()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('browser_name')->defaultValue('%mink.browser_name%')->end()
                ->scalarNode('wd_host')->defaultValue('http://localhost:4444/wd/hub')->end()
                ->arrayNode('capabilities')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDriver(array $config)
    {
        if (!class_exists('Mink\WebdriverClassicDriver\WebdriverClassicDriver')) {
            throw new \RuntimeException(
                'Install mink/webdriver-classic-driver in order to use the webdriver_classic driver.'
            );
        }

        return new Definition('Pim\Behat\Extension\AkeneoWebdriverClassicDriver', [
            $config['browser_name'],
            $config['capabilities'],
            $config['wd_host'],
        ]);
    }
}
