<?php

namespace RocketLauncherHooksExtractor\Services;

use League\Flysystem\Filesystem;
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
    public function extract(Folder $folder, array $exclusions): array {
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
        if(! preg_match_all('', $content, $results)) {
            return [];
        }

        $actions = $results['action'];
        $docblocks = $results['docblock'];

        foreach ($actions as $index => $action) {
            $docblock = $docblocks[$index];
            $docblock = $this->parse_docblock($docblock);
        }
    }

    public function extract_filters(string $content): array {

    }

    public function parse_docblock(string $content): array {

    }
}
