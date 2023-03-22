<?php

namespace RocketLauncherHooksExtractor\Services;

use League\Flysystem\Filesystem;
use RocketLauncherHooksExtractor\ObjectValues\Path;

class OutputWriter
{
    public function write_output(array $hooks, Path $output = null, Path $input = null): void {
        if($input) {
            $output_content = $this->initialize_output($input);
        }else {
            $output_content = [

            ];
        }

        if( key_exists('hooks', $hooks) ) {
            $hooks['hooks'] = array_merge($hooks['hooks'], $hooks);
        } else {
            $hooks['hooks'] = $hooks;
        }

        $output_file = $output ? $output->get_value() : 'hooks-output.yml';
        yaml_emit($output_file, $output_content);
    }

    protected function initialize_output(Path $path) {
        $content = yaml_parse_file($path->get_value());
        if(! $content) {
            return [];
        }
        return $content;
    }
}
