<?php

namespace Oro\Bundle\SecurityBundle\Annotation\Loader;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage;
use Symfony\Component\Finder\Finder;

class AclAnnotationLoader extends AbstractLoader implements AclAnnotationLoaderInterface
{
    final public const string ANCESTOR_CLASS = AclAncestor::class;

    /**
     * @param string[] $bundles A list of loaded bundles
     * @param string[] $subDirs A list of sub directories (related to a bundle directory)
     *                          where classes carrying #[AclAncestor] may be located
     */
    public function __construct($bundles, private readonly array $subDirs)
    {
        parent::__construct($bundles);
    }

    /**
     * Loads ACL annotations from PHP files
     */
    public function load(AclAnnotationStorage $storage)
    {
        if ($this->subDirs !== []) {
            $directories = [];
            foreach ($this->bundleDirectories as $bundleDir) {
                foreach ($this->subDirs as $subDir) {
                    $dir = $bundleDir . DIRECTORY_SEPARATOR . $subDir;
                    if (is_dir($dir)) {
                        $directories[] = $dir;
                    }
                }
            }
        } else {
            $directories = $this->bundleDirectories;
        }

        $files = $this->findFiles('*.php', $directories);

        foreach ($files as $file) {
            $className = $this->getClassName($file);
            if ($className !== null) {
                $reflection = $this->getReflectionClass($className);
                foreach ($reflection->getAttributes(self::ANCESTOR_CLASS) as $attribute) {
                    $storage->addAncestor($attribute->newInstance(), $reflection->getName());
                }
                foreach ($reflection->getMethods() as $reflectionMethod) {
                    foreach ($reflectionMethod->getAttributes(self::ANCESTOR_CLASS) as $attribute) {
                        $storage->addAncestor(
                            $attribute->newInstance(),
                            $reflection->getName(),
                            $reflectionMethod->getName()
                        );
                    }
                }
            }
        }
    }

    /**
     * Gets a class name from the given file.
     *
     * Restrictions:
     *      - only one class must be declared in a file
     *      - a namespace must be declared in a file
     *
     * @param  string      $fileName
     * @return null|string the fully qualified class name or null if the class name cannot be extracted
     */
    protected function getClassName($fileName): ?string
    {
        $src = $this->getFileContent($fileName);
        if (!str_contains($src, 'AclAncestor')) {
            return null;
        }

        if (!preg_match('/\bnamespace\s+([^;]+);/s', $src, $match)) {
            return null;
        }
        $namespace = $match[1];

        if (!preg_match('/\bclass\s+([^\s]+)\s+(?:extends|implements|{)/s', $src, $match)) {
            return null;
        }

        return $namespace . '\\' . $match[1];
    }

    /**
     * Creates ReflectionClass object
     *
     * @param  string           $className
     * @return \ReflectionClass
     */
    protected function getReflectionClass($className)
    {
        return new \ReflectionClass($className);
    }

    /**
     * Reads the given file into a string
     *
     * @param  string $fileName
     * @return string
     */
    protected function getFileContent($fileName)
    {
        return file_get_contents($fileName);
    }

    /**
     * @param $filePattern
     * @return array
     */
    private function findFiles($filePattern, array $dirs)
    {
        $finder = new Finder();
        $finder
            ->files()
            ->name($filePattern)
            ->in($dirs)
            ->ignoreVCS(true);

        $result = [];
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $result[] = $file->getRealPath();
        }

        return $result;
    }
}
