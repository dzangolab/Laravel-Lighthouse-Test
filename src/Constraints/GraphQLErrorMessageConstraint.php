<?php

namespace Knevelina\LighthouseTest\Constraints;

use PHPUnit\Framework\Constraint\Constraint;

class GraphQLErrorMessageConstraint extends Constraint
{
    /**
     * The expected GraphQL error message.
     *
     * @var string
     */
    private $message;

    /**
     * Construct the constraint.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function toString(): string
    {
        return sprintf('GraphQL error with message "%s"', $this->message);
    }

    public function matches($other): bool
    {
        if (!is_array($other)) {
            return false;
        }

        foreach ($other as $error) {
            if (($error['message'] ?? null) === $this->message) {
                return true;
            }
        }

        return false;
    }

    public function failureDescription($other): string
    {
        if (!is_array($other) || count($other) === 0) {
            return sprintf(
                'GraphQL error with message "%s" is returned. No error was returned.',
                $this->message
            );
        }

        $messages = implode(', ', array_map(function ($error) {
            return $error['message'] ?? '<unknown message>';
        }, $other));

        return sprintf(
            'GraphQL error with message "%s" is returned. The returned errors were %s.',
            $this->message,
            $messages
        );
    }
}