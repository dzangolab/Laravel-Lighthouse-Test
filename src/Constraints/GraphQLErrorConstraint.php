<?php

namespace Knevelina\LighthouseTest\Constraints;

use PHPUnit\Framework\Constraint\Constraint;

class GraphQLErrorConstraint extends Constraint
{
    public function toString(): string
    {
        return 'GraphQL error';
    }

    public function matches($other): bool
    {
        if (!is_array($other)) {
            return false;
        }

        return count($other) > 0;
    }

    public function failureDescription($other): string
    {
        if (is_array($other)) {
            return 'GraphQL error is returned. Empty errors array was returned.';
        }
        return 'GraphQL error is returned. No error was returned.';
    }
}