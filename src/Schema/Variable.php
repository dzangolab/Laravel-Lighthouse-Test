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
     * The variable's type.
     * 
     * @var string
     */
    protected $type;

    /**
     * Construct a new variable.
     *
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Get the name of the variable.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the type of the variable.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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