<?php

namespace Knevelina\LighthouseTest\Traits;

use Illuminate\Foundation\Testing\TestCase;
use Knevelina\LighthouseTest\Constraints\GraphQLErrorMessageConstraint;

trait InterpretsGraphQLResponses
{
    public function assertHasGraphQLErrorMessage($response, string $message)
    {
        /** @var TestCase $this */
        static::assertThat($response->json('errors'), new GraphQLErrorMessageConstraint($message));
    }
}