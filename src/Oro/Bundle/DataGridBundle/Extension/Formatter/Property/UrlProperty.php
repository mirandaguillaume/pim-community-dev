<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlProperty extends AbstractProperty
{
    final public const ROUTE_KEY = 'route';
    final public const IS_ABSOLUTE_KEY = 'isAbsolute';
    final public const ANCHOR_KEY = 'anchor';
    final public const PARAMS_KEY = 'params';

    /** @var array */
    protected $excludeParams = [self::ROUTE_KEY, self::IS_ABSOLUTE_KEY, self::ANCHOR_KEY, self::PARAMS_KEY];

    public function __construct(protected UrlGeneratorInterface $router)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValue(ResultRecordInterface $record)
    {
        $route = $this->router->generate(
            $this->get(self::ROUTE_KEY),
            $this->getParameters($record),
            $this->getOr(self::IS_ABSOLUTE_KEY, UrlGeneratorInterface::ABSOLUTE_PATH)
        );

        return $route . $this->getOr(self::ANCHOR_KEY);
    }

    /**
     * Get route parameters from record
     */
    protected function getParameters(ResultRecordInterface $record): array
    {
        $result = [];
        foreach ($this->getOr(self::PARAMS_KEY, []) as $name => $dataKey) {
            if (is_numeric($name)) {
                $name = $dataKey;
            }
            $result[$name] = $record->getValue($dataKey);
        }

        return $result;
    }
}
