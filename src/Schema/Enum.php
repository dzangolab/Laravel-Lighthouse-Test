<?php

namespace Knevelina\LighthouseTest\Schema;

/**
 * An enum value to be used in a constructed GraphQL query.
 */
class Enum
{
    /**
     * The enum value.
     *
     * @var string
     */
    protected $value;

    /**
     * Construct a new enum value.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Get the GraphQL format of the enum value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}