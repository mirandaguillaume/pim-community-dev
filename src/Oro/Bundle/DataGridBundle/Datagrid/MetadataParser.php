<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\Routing\RouterInterface;

/**
 * Metadata parser for grid data
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetadataParser
{
    final public const ROUTE = 'oro_datagrid_index';

    public function __construct(private readonly FragmentHandler $fragmentHandler, private readonly Manager $manager, private readonly RequestParameters $requestParams, private readonly RouterInterface $router)
    {
    }

    /**
     * Returns grid metadata array
     *
     *
     */
    public function getGridMetadata(string $name, array $params = []): array
    {
        $metaData = $this->manager->getDatagrid($name)->getMetadata();
        $metaData->offsetAddToArray('options', ['url' => $this->generateUrl($name, $params)]);

        return $metaData->toArray();
    }

    /**
     * Renders grid data using internal request
     * We add additional params form current request to avoid two request on page refresh
     *
     *
     */
    public function getGridData(string $name, array $params = []): string
    {
        return $this->fragmentHandler->render($this->generateUrl($name, $params));
    }

    /**
     * @param bool   $mixRequest
     *
     */
    protected function generateUrl(string $name, array $params): string
    {
        $additional = $this->requestParams->getRootParameterValue();

        $params = [
            $name      => array_merge($params, $additional),
            'gridName' => $name
        ];

        return $this->router->generate(self::ROUTE, $params);
    }
}
