<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

use Atoolo\Resource\ResourceBaseLocator;
use Symfony\Component\Finder\Finder;

/**
 * The LocationFinder searches recursively for files that represent a
 * resource. The corresponding files are
 * recognized and returned according to certain rules.
 *
 * The rules are:
 * - files must have the extension `.php
 * - files must not be located in the `WEB-IES` directory
 * - files must not have the name `.*-1015t.php.*`
 */
class LocationFinder
{
    public function __construct(
        private readonly ResourceBaseLocator $baseLocator
    ) {
    }

    /**
     * @return string[]
     */
    public function findAll(): array
    {

        $finder = new Finder();
        $finder->in($this->getBasePath())->exclude('WEB-IES');
        $finder->name('*.php');
        $finder->notPath('#.*-1015t.php.*#'); // preview files
        $finder->files();

        $pathList = [];
        foreach ($finder as $file) {
            $pathList[] = $this->toRelativePath($file->getPathname());
        }

        sort($pathList);

        return $pathList;
    }

    /**
     * @param string[] $paths
     * @return string[]
     */
    public function findPaths(array $paths): array
    {
        $pathList = [];

        $directories = [];

        $finder = new Finder();
        foreach ($paths as $path) {
            if (!str_starts_with($path, '/')) {
                $path = '/' . $path;
            }
            $absolutePath = $this->getBasePath() . $path;
            if (is_file($absolutePath)) {
                $pathList[] = $path;
                continue;
            }
            if (is_dir($absolutePath)) {
                $directories[] = $path;
            }
        }

        if (empty($directories)) {
            return $pathList;
        }

        foreach ($directories as $directory) {
            $finder->in($this->getBasePath() . $directory);
        }
        $finder->name('*.php');
        $finder->notPath('#.*-1015t.php.*#'); // preview files
        $finder->files();

        foreach ($finder as $file) {
            $pathList[] = $this->toRelativePath($file->getPathname());
        }

        sort($pathList);

        return $pathList;
    }

    private function getBasePath(): string
    {
        return rtrim($this->baseLocator->locate(), '/');
    }

    private function toRelativePath(string $path): string
    {
        return substr($path, strlen($this->getBasePath()));
    }
}
