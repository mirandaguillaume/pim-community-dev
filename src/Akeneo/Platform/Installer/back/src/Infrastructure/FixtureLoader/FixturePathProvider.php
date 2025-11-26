<?php

namespace Akeneo\Platform\Installer\Infrastructure\FixtureLoader;

/**
 * Provides the path of the data fixtures.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixturePathProvider
{
    /**
     * @param array<string, class-string> $bundles
     */
    public function __construct(protected array $bundles)
    {
    }

    /**
     * Get the path of the data used by the installer.
     */
    public function getFixturesPath(string $catalogPath): string
    {
        $installerDataDir = $catalogPath;

        if (preg_match('/^(?P<bundle>\w+):(?P<directory>\w+)$/', $catalogPath, $matches)) {
            $reflection = new \ReflectionClass($this->bundles[$matches['bundle']]);
            $installerDataDir = dirname($reflection->getFilename()).'/Resources/fixtures/'.$matches['directory'];
        }

        if (!is_dir($installerDataDir)) {
            throw new \RuntimeException('Installer data directory cannot be found.');
        }

        if (DIRECTORY_SEPARATOR !== substr($installerDataDir, -1, 1)) {
            $installerDataDir .= DIRECTORY_SEPARATOR;
        }

        return $installerDataDir;
    }
}
