<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\LogRecord;

/**
 * Write the log into a separate log file
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class BatchLogHandler extends StreamHandler
{
    /** @var string */
    protected $filename;

    protected string $logDir;

    public function __construct(
        int|string|Level $level,
        bool $bubble,
        ?int $filePermission,
        bool $useLocking,
        string $logDir
    ) {
        $this->logDir = $logDir;

        $url = $this->getRealPath($this->generateLogFilename());
        parent::__construct($url, $level, $bubble, $filePermission, $useLocking);
    }

    /**
     * Get the filename of the log file
     *
     * @return string
     */
    public function getFilename(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $subDirectory
     */
    public function setSubDirectory($subDirectory): void
    {
        $this->close();
        $this->url = $this->getRealPath($this->generateLogFilename(), $subDirectory);
    }

    /**
     * Get the real path of the log file
     *
     * @param string $filename
     * @param string $subDirectory
     *
     * @return string
     */
    private function getRealPath($filename, $subDirectory = null): string
    {
        if (null !== $subDirectory) {
            return sprintf('%s/%s/%s', $this->logDir, $subDirectory, $filename);
        }

        return sprintf('%s/%s', $this->logDir, $filename);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function write(LogRecord $record): void
    {
        if (null === $this->url) {
            $this->url = $this->getRealPath($this->generateLogFilename());
        }

        if (!is_dir(dirname($this->url))) {
            mkdir(dirname($this->url), 0o755, true);
        }

        parent::write($record);
    }

    /**
     * Generates a random filename
     *
     * @return string
     */
    private function generateLogFilename(): string
    {
        return sprintf('batch_%s.log', sha1(uniqid((string) random_int(0, mt_getrandmax()), true)));
    }
}
