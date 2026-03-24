<?php

namespace Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\FilesystemReader;

/**
 * Fetch the raw file of a file stored in a virtual filesystem
 * into the local filesystem.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileFetcherInterface
{
    /**
     * @param string              $fileKey
     *
     * @throws FileTransferException
     * @throws \LogicException
     *
     * @return \SplFileInfo
     */
    public function fetch(FilesystemReader $filesystem, $fileKey, array $options = []);
}
