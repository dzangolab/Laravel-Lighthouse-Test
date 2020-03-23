<?php

namespace Knevelina\LighthouseTest\Traits;

use Knevelina\LighthouseTest\Constraints\GraphQLErrorConstraint;
use Knevelina\LighthouseTest\Constraints\GraphQLErrorMessageConstraint;

trait InterpretsGraphQLResponses
{
    public function assertHasGraphQLErrorMessage($response, string $message)
    {
        static::assertThat($response->json('errors'), new GraphQLErrorMessageConstraint($message));
    }

    public function assertHasGraphQLError($response)
    {
        static::assertThat($response->json('errors'), new GraphQLErrorConstraint());
    }
}