<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Factory;

use Akeneo\Tool\Component\Versioning\Model\Version;
use Ramsey\Uuid\UuidInterface;

/**
 * Version factory
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionFactory
{
    /**
     * @param string $versionClass
     */
    public function __construct(protected $versionClass) {}

    /**
     * Create a version
     *
     * @param  string  $resourceName
     * @param  string  $author
     *
     * @return Version
     */
    public function create($resourceName, mixed $resourceId, ?UuidInterface $resourceUuid, $author, mixed $context = null)
    {
        return new $this->versionClass($resourceName, $resourceId, $resourceUuid, $author, $context);
    }
}
