<?php

namespace RocketLauncherHooksExtractor\Services;

use Jasny\PhpdocParser\PhpdocException;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\Summery;
use League\Flysystem\Filesystem;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Parser;
use RocketLauncherHooksExtractor\Entities\Configuration;
use RocketLauncherHooksExtractor\ObjectValues\Folder;

class Extractor
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @param Filesystem $filesystem
     * @param Parser $parser
     */
    public function __construct(Filesystem $filesystem, Parser $parser)
    {
        $this->filesystem = $filesystem;
        $this->parser = $parser;
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

                $token = $this->parser->parseSourceFile($content_file);

                $hooks = array_merge($hooks, $this->extract_actions($content_file, $content['path']));
                $hooks = array_merge($hooks, $this->extract_filters($content_file, $content['path']));
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

    public function extract_actions(Node $node, string $path): array {
        $extracts = [];
        $actions = [];
        foreach ($node->getChildNodes() as $child_node) {
            if (! $child_node instanceof Node\Expression\CallExpression || $child_node->getText() !== 'do_action') {
                continue;
            }

            $actions []= $child_node;
        }

        foreach ($actions as $action) {

            $parameters = explode(',', $action->argumentExpressionList->getText());

            if(count($parameters) === 0) {
                continue;
            }

            $name = trim( trim(array_shift($parameters)), '\'"');

            $extract = [
                'type' => 'action',
                'name' => $name,
                'files' => [
                    [
                        'path' => $path,
                        'line' => $action->getStartPosition(),
                    ]
                ],
            ];

            $docblock = $this->get_doc_node($action);
            $docblock = $this->parse_docblock($docblock);
            $extracts[] = array_merge( $extract, $docblock );
        }

        return $extracts;
    }

    public function extract_filters(Node $node, string $path): array {
        $extracts = [];
        $filters = [];

        foreach ($node->getChildNodes() as $child_node) {
            if (! $child_node instanceof Node\Expression\CallExpression || $child_node->getText() !== 'apply_filters') {
                continue;
            }

            $filters []= $child_node;
        }

        foreach ($filters as $filter) {

            $parameters = explode(',', $filter->argumentExpressionList->getText());

            if(count($parameters) === 0) {
                continue;
            }

            $name = trim( trim(array_shift($parameters)), '\'"');

            $extract = [
                'type' => 'filter',
                'name' => $name,
                'files' => [
                    [
                        'path' => $path,
                        'line' => $filter->getStartPosition(),
                    ]
                ],
            ];

            $docblock = $this->get_doc_node($filter);
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

    protected function find_line(int $offset, string $content) {
        list($before) = str_split($content, $offset); // fetches all the text before the match

        return strlen($before) - strlen(str_replace("\n", "", $before)) + 1;
    }

    protected function get_doc_node(Node $node): string {
        while ($node && ! $node instanceof ExpressionStatement) {

            $children = $node->getChildNodes();
            foreach ($children as $child) {
                $doc = $child->getDocCommentText();
                if($doc) {
                    return $doc;
                }
            }

            $node = $node->getParent();
        }

        return '';
    }
}
