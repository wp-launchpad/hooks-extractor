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
            $output_content['hooks'] = array_merge($hooks['hooks'], $hooks);
        } else {
            $output_content['hooks'] = $hooks;
        }

        $output_content['hooks'] = array_values($this->unique_multidim_array($output_content['hooks'], 'name'));

        $output_file = $output ? $output->get_value() : 'hooks-output.yml';
        $yaml_content = yaml_emit($output_content);
        echo $yaml_content;
        yaml_emit_file($output_file, $output_content);
    }

    protected function initialize_output(Path $path) {
        $content = yaml_parse_file($path->get_value());
        if(! $content) {
            return [];
        }
        return $content;
    }

    protected function unique_multidim_array($array, $key) {

        $temp_array = array();

        $i = 0;

        $key_array = array();



        foreach($array as $val) {

            if (!in_array($val[$key], $key_array)) {

                $key_array[$i] = $val[$key];

                $temp_array[$i] = $val;
                $i++;
                continue;
            }
            $index = array_search($val[$key], $key_array);
            $lines = array_merge( $temp_array[$index]['files'], $val['files']);

            if(count($val) > count($temp_array[$index])) {
                $temp_array[$index] = $val;
            }

            $temp_array[$index]['files'] = $lines;
            $i ++;
        }

        return $temp_array;

    }
}
