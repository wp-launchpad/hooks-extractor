<?php

namespace RocketLauncherHooksExtractor\Services;

use League\Flysystem\Filesystem;
use RocketLauncherHooksExtractor\Entities\Configuration;
use RocketLauncherHooksExtractor\ObjectValues\Folder;
use RocketLauncherHooksExtractor\ObjectValues\Path;
use RocketLauncherHooksExtractor\ObjectValues\Prefix;

class ConfigurationLoader
{
    /**
     * @var Filesystem
     */
    protected $app_filesystem;

    /**
     * @var Filesystem
     */
    protected $project_filesystem;

    /**
     * @param Filesystem $app_filesystem
     * @param Filesystem $project_filesystem
     */
    public function __construct(Filesystem $app_filesystem, Filesystem $project_filesystem)
    {
        $this->app_filesystem = $app_filesystem;
        $this->project_filesystem = $project_filesystem;
    }

    public function load(Path $path = null): Configuration {
        $files = [
            new Path($this->project_filesystem->getAdapter()->getPathPrefix() . '/hook-extractor.yml'),
            new Path($this->app_filesystem->getAdapter()->getPathPrefix() . '/configs/default.yml'),
        ];

        if($path) {
            array_unshift($files, $path);
        }

        foreach ($files as $file) {
            $configuration = $this->load_configs($file);
            if( $configuration ) {
                return $configuration;
            }
        }

        throw new NoConfigurationException('No configuration found');
    }

    protected function load_configs(Path $path) {
        $configs = yaml_parse_file($path->get_value());
        if(! $configs) {
            return null;
        }

        if(! key_exists('includes', $configs)) {
            return null;
        }

        $configurations = new Configuration();

        $includes = array_map(function ($include) {
            return new Folder($include);
        }, $configs['includes']);

        $configurations->setFolders($includes);

        if(key_exists('excludes', $configs)) {
            $excludes = array_map(function ($exclude) {
                return $this->project_filesystem->has($exclude) ? new Path($exclude) : new Folder($exclude);
            }, $configs['excludes']);

            $configurations->setExclusions($excludes);
        }

        if( ! key_exists('prefix', $configs) ) {
            return $configurations;
        }

        $prefixes = array_map(function ($prefix) {
            return new Prefix($prefix);
        }, $configs['prefix']);

        $configurations->setPrefixes($prefixes);

        return $configurations;
    }
}
