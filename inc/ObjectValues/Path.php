<?php

namespace RocketLauncherHooksExtractor\ObjectValues;

class Path implements Content
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function get_value(): string
    {
        return $this->value;
    }
}
