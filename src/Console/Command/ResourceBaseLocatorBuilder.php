<?php

declare(strict_types=1);

namespace Atoolo\Search\Console\Command;

use Atoolo\Resource\Loader\ServerVarResourceBaseLocator;
use Atoolo\Resource\ResourceBaseLocator;

class ResourceBaseLocatorBuilder
{
    public function build(string $resourceDir): ResourceBaseLocator
    {
        $subDirectory = null;
        if (is_dir($resourceDir . '/objects')) {
            $subDirectory = 'objects';
        }
        $_SERVER['RESOURCE_ROOT'] = $resourceDir;
        return new ServerVarResourceBaseLocator(
            'RESOURCE_ROOT',
            $subDirectory
        );
    }
}
