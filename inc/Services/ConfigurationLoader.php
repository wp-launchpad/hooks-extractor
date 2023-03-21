<?php

namespace RocketLauncherHooksExtractor\Services;

use RocketLauncherHooksExtractor\ObjectValues\Path;

class ConfigurationLoader
{
    public function load(Path $path): array {
        if($path) {

        }
    }

    protected function load_configs(Path $path) {
        $configs = yaml_parse_file($path->get_value());
        if(! $configs) {
            return null;
        }

    }
}
