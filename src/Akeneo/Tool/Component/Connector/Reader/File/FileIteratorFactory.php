<?php

namespace Akeneo\Tool\Component\Connector\Reader\File;

/**
 * Factory to create file iterators
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileIteratorFactory
{
    /** @var string */
    protected $className;

    /**
     * Configure the factory with a class name
     *
     * @param string $className
     * @param string $type
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($className, protected $type)
    {
        $interface = '\\' . \Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface::class;
        if (!is_subclass_of($className, $interface)) {
            throw new \InvalidArgumentException(sprintf('%s must implement %s', $className, $interface));
        }

        $this->className = $className;
    }

    /**
     * Create a file iterator instance
     *
     * @param string $filePath
     *
     * @return FileIteratorInterface
     */
    public function create($filePath, array $options = [])
    {
        return new $this->className($this->type, $filePath, $options);
    }
}
