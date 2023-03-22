<?php

namespace RocketLauncherHooksExtractor;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use RocketLauncherBuilder\App;
use RocketLauncherBuilder\Entities\Configurations;
use RocketLauncherBuilder\ServiceProviders\ServiceProviderInterface;
use RocketLauncherHooksExtractor\Commands\ExtractHooksCommand;
use RocketLauncherHooksExtractor\Services\ConfigurationLoader;
use RocketLauncherHooksExtractor\Services\Extractor;
use RocketLauncherHooksExtractor\Services\OutputWriter;

class ServiceProvider implements ServiceProviderInterface
{

    /**
     * Interacts with the filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Interacts with the filesystem.
     *
     * @var Filesystem
     */
    protected $app_filesystem;


    /**
     * Configuration from the project.
     *
     * @var Configurations
     */
    protected $configs;

    /**
     * Instantiate the class.
     *
     * @param Configurations $configs configuration from the project.
     * @param Filesystem $filesystem Interacts with the filesystem.
     * @param string $app_dir base directory from the cli.
     */
    public function __construct(Configurations $configs, Filesystem $filesystem, string $app_dir)
    {
        $this->configs = $configs;
        $this->filesystem = $filesystem;

        $adapter = new Local(
        // Determine root directory
            __DIR__ . '/../'
        );

        // The FilesystemOperator
        $this->app_filesystem = new Filesystem($adapter);

    }

    public function attach_commands(App $app): App
    {
        $extractor = new Extractor($this->filesystem);
        $configuration_loader = new ConfigurationLoader($this->app_filesystem, $this->filesystem);
        $output_writer = new OutputWriter();
        $app->add(new ExtractHooksCommand($extractor, $configuration_loader, $output_writer));
        return $app;
    }
}
