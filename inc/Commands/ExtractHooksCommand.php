<?php

namespace RocketLauncherHooksExtractor\Commands;
use RocketLauncherBuilder\Commands\Command;
use RocketLauncherHooksExtractor\ObjectValues\InvalidValue;
use RocketLauncherHooksExtractor\ObjectValues\Path;
use RocketLauncherHooksExtractor\Services\ConfigurationLoader;
use RocketLauncherHooksExtractor\Services\Extractor;
use RocketLauncherHooksExtractor\Services\OutputWriter;

class ExtractHooksCommand extends Command
{
    /**
     * @var Extractor
     */
    protected $extractor;

    /**
     * @var ConfigurationLoader
     */
    protected $configuration_loader;

    /**
     * @var OutputWriter
     */
    protected $output_writer;

    public function __construct(Extractor $extractor, ConfigurationLoader $configuration_loader, OutputWriter $output_writer)
    {
        parent::__construct('hook:extract', 'Extract hooks');

        $this->extractor = $extractor;
        $this->configuration_loader = $configuration_loader;

        $this->output_writer = $output_writer;

        $this
            ->option('-i --input', 'Path to an existing file to take as input')
            ->option('-o --output', 'Path to the output file')
            ->option('-c --configurations', 'Path to the configurations file')
            // Usage examples:
            ->usage(
            // append details or explanation of given example with ` ## ` so they will be uniformly aligned when shown
                '<bold>  $0 hook:extract</end> ## Extract hooks<eol/>'
            );
    }

    public function execute($input, $output, $configurations){

        list($input, $error) = $this->validate_param($input, 'The input is invalid');
        if($error) {
            return;
        }
        list($output, $error) = $this->validate_param($output, 'The output is invalid');
        if($error) {
            return;
        }
        list($configurations, $error) = $this->validate_param($configurations, 'The configurations is invalid');
        if($error) {
            return;
        }

        $configurations = $this->configuration_loader->load($configurations);

        $extracted_hooks = $this->extractor->extract($configurations);

        $this->output_writer->write_output($extracted_hooks, $output, $input);
    }

    protected function validate_param($input, string $message) {
        if(! $input) {
            return [$input, false];
        }

        try {
            return [new Path($input), false];
        } catch (InvalidValue $exception) {
            $this->app()->io()->error($message);
            return [null, true];
        }
    }
}
