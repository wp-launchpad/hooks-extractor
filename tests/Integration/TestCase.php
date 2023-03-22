<?php

namespace RocketLauncherHooksExtractor\Tests\Integration;

use ReflectionObject;
use RocketLauncherBuilder\AppBuilder;
use RocketLauncherHooksExtractor\ServiceProvider;
use WPMedia\PHPUnit\Unit\VirtualFilesystemTestCase;

abstract class TestCase extends VirtualFilesystemTestCase
{

    protected $config;

    protected function setUp(): void {
        parent::setUp();

        if ( empty( $this->config ) ) {
            $this->loadTestDataConfig();
        }

        $this->init();

    }

    public function configTestData() {
        if ( empty( $this->config ) ) {
            $this->loadTestDataConfig();
        }

        return isset( $this->config['test_data'] )
            ? $this->config['test_data']
            : $this->config;
    }

    protected function launch_app(string $command) {

        list($command, $args_mappings) = $this->save_param_with_spaces($command);

        $argv = array_merge(['index.php'], explode(' ', $command));

        foreach ($args_mappings as $id => $value) {
            foreach ($argv as $index => $arg) {
                $argv[$index] = str_replace($id, $value, $arg);
            }
        }
        $_SERVER['argv'] = $argv;
        AppBuilder::enable_test_mode();
        AppBuilder::init(/*$this->rootVirtualUrl*/ __DIR__ . '/../../wp-content/plugins/wp-rocket', [
            ServiceProvider::class,
        ]);
        unset($_SERVER['argv']);
    }

    protected function save_param_with_spaces(string $command) {
        if ( ! preg_match_all('/(?<content>"[^"]*")/m', $command, $results)) {
            return [$command, []];
        }
        $args_mapping = [];

        foreach ($results['content'] as $content) {
            $id = uniqid('args_mapping_');
            $args_mapping[$id] = $content;
        }

        foreach ($args_mapping as $id => $value) {
            $command = str_replace($value, $id, $command);
        }

        return [$command, $args_mapping];
    }

    protected function loadTestDataConfig() {
        $obj      = new ReflectionObject( $this );
        $filename = $obj->getFileName();

        $this->config = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
    }
}
