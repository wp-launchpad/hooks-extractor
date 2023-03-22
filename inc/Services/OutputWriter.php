<?php

namespace RocketLauncherHooksExtractor\Services;

use League\Flysystem\Filesystem;
use RocketLauncherHooksExtractor\ObjectValues\Path;

class OutputWriter
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function write_output(array $hooks, Path $output = null, Path $input = null): void {

    }
}