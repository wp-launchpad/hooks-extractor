<?php

namespace RocketLauncherHooksExtractor\Services;

use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use League\Flysystem\Filesystem;
use RocketLauncherHooksExtractor\Entities\Configuration;
use RocketLauncherHooksExtractor\ObjectValues\Folder;

class Extractor
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

    /**
     * @return array
     */
    public function extract(Configuration $configuration): array {
        $hooks = [];
        $contents = $this->filesystem->listContents($folder->get_value(), true);
        foreach ($contents as $content) {
            if($content['type'] === 'dir') {
                continue;
            }

            if($content['type'] !== 'file' || $content['extension'] !== 'php') {
                continue;
            }

            $content_file = $this->filesystem->read($content['path']);

            $hooks[] = $this->extract_actions($content_file);
            $hooks[] = $this->extract_filters($content_file);
        }

        return $hooks;
    }

    public function extract_actions(string $content): array {
        $extracts = [];
        if(! preg_match_all('#(?<docblock>/\*(?:[^*]|\n|(?:\*(?:[^/]|\n)))*\*/)?do_action\(\s*[\'"](?<action>[^\'"]*)[\'"]\s*\)#', $content, $results)) {
            return [];
        }

        $actions = $results['action'];
        $docblocks = $results['docblock'];

        foreach ($actions as $index => $action) {
            $extract = [
                'type' => 'action',
                'name' => $action,
            ];

            $docblock = $docblocks[$index];
            $docblock = $this->parse_docblock($docblock);

            $extracts[] = array_merge( $extract, $docblock );
        }

        return $extracts;
    }

    public function extract_filters(string $content): array {
        $extracts = [];
        if(! preg_match_all('#(?<docblock>/\*(?:[^*]|\n|(?:\*(?:[^/]|\n)))*\*/)?apply_filters\(\s*[\'"](?<filter>[^\'"]*)[\'"]\s*\)#', $content, $results)) {
            return [];
        }

        $filters = $results['filter'];
        $docblocks = $results['docblock'];

        foreach ($filters as $index => $filter) {
            $extract = [
                'type' => 'filter',
                'name' => $filter,
            ];

            $docblock = $docblocks[$index];
            $docblock = $this->parse_docblock($docblock);

            $extracts[] = array_merge( $extract, $docblock );
        }

        return $extracts;
    }

    public function parse_docblock(string $content): array {
        $tags = PhpDocumentor::tags();
        $parser = new PHPDocParser($tags);
        return $parser->parse($content);
    }
}
