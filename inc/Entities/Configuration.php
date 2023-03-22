<?php

namespace RocketLauncherHooksExtractor\Entities;

use RocketLauncherHooksExtractor\ObjectValues\Content;
use RocketLauncherHooksExtractor\ObjectValues\Folder;
use RocketLauncherHooksExtractor\ObjectValues\Prefix;

class Configuration
{
    /**
     * @var Folder[]
     */
    protected $folders;

    /**
     * @var Content[]
     */
    protected $exclusions;

    /**
     * @var Prefix[]
     */
    protected $prefixes;

    /**
     * @var Prefix[]
     */
    protected $hook_excluded;

    /**
     * @return Folder[]
     */
    public function getFolders(): array
    {
        return $this->folders;
    }

    /**
     * @param Folder[] $folders
     */
    public function setFolders(array $folders): void
    {
        $this->folders = $folders;
    }

    /**
     * @return Content[]
     */
    public function getExclusions(): array
    {
        return $this->exclusions;
    }

    /**
     * @param Content[] $exclusions
     */
    public function setExclusions(array $exclusions): void
    {
        $this->exclusions = $exclusions;
    }

    /**
     * @return Prefix[]
     */
    public function getPrefixes(): array
    {
        return $this->prefixes;
    }

    /**
     * @param Prefix[] $prefixes
     */
    public function setPrefixes(array $prefixes): void
    {
        $this->prefixes = $prefixes;
    }

    /**
     * @return Prefix[]
     */
    public function get_hook_excluded(): array
    {
        return $this->hook_excluded;
    }

    /**
     * @param Prefix[] $hook_excluded
     */
    public function set_hook_excluded(array $hook_excluded): void
    {
        $this->hook_excluded = $hook_excluded;
    }

}
