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


}
