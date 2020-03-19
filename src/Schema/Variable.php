<?php

namespace Knevelina\LighthouseTest\Schema;

/**
 * An variable to be used in a constructed GraphQL query.
 */
class Variable
{
    /**
     * The variable name.
     *
     * @var string
     */
    protected $name;

    /**
     * Construct a new variable.
     *
     * @param string $value
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the GraphQL format of the variable.
     *
     * @return string
     */
    public function __toString()
    {
        return '$' . $this->name;
    }
}