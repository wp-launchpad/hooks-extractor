<?php

namespace RocketLauncherHooksExtractor\Services;

use Jasny\PhpdocParser\PhpdocException;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\Summery;
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
        foreach ($configuration->getFolders() as $folder) {
            $contents = $this->filesystem->listContents($folder->get_value(), true);
            foreach ($contents as $content) {
                if($content['type'] === 'dir') {
                    continue;
                }

                if($content['type'] !== 'file' || $content['extension'] !== 'php') {
                    continue;
                }

                if($this->is_excluded($content['path'], $configuration->getExclusions())) {
                    continue;
                }

                $content_file = $this->filesystem->read($content['path']);

                $hooks = array_merge($hooks, $this->extract_actions($content_file));
                $hooks = array_merge($hooks, $this->extract_filters($content_file));
            }
        }

        return $this->remove_excluded($hooks, $configuration);
    }

    protected function is_excluded(string $path, array $exclusions): bool {
        foreach ($exclusions as $exclusion) {
            if(preg_match("#^{$exclusion->get_value()}#", $path)) {
                return true;
            }
        }
        return false;
    }

    public function extract_actions(string $content): array {
        $extracts = [];
        if(! preg_match_all('#(?<docblock>\/\*(?:[^*]|(?:\*[^\/]))*\*\/)?\s*do_action\s*\(\s*[\'"](?<action>[^\'"]*)[\'"](?:\s*,\s*\$[a-zA-Z_]\w*)*\s*\)#', $content, $results)) {
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
        if(! preg_match_all('#(?<docblock>\/\*(?:[^*]|(?:\*[^\/]))*\*\/)?\s*[\w\)\(=>\'"\$\h]*?apply_filters\s*\(\s*[\'"](?<filter>[^\'"]*)[\'"](?:\s*,\s*\$[a-zA-Z_]\w*)*\s*\)#', $content, $results)) {
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
        $tags = PhpDocumentor::tags()->with([new Summery()]);
        $parser = new PHPDocParser($tags);
        try {
            return $parser->parse($content);
        } catch (PhpdocException $exception) {
            return [];
        }
    }

    public function remove_excluded(array $hooks, Configuration $configuration) {
        $excluded = $configuration->get_hook_excluded();
        $excluded = array_map(function ($excluded) {
            return $excluded->get_value();
        }, $excluded);

        return array_values(array_filter($hooks, function ($hook) use ($excluded) {
                  if(in_array($hook['name'], $excluded)) {
                      return false;
                  }
                  return $hook;
        }));
    }
}
