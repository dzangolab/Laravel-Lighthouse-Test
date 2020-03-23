<?php

namespace Knevelina\LighthouseTest\Traits;

use Knevelina\LighthouseTest\Constraints\GraphQLErrorConstraint;
use Knevelina\LighthouseTest\Constraints\GraphQLErrorMessageConstraint;

trait InterpretsGraphQLResponses
{
    /**
     * Assert that the response has a specific error message.
     *
     * @param TestResponse $response
     * @param string $message
     * @return void
     */
    public function assertHasGraphQLErrorMessage($response, string $message)
    {
        static::assertThat($response->json('errors'), new GraphQLErrorMessageConstraint($message));
    }

    /**
     * Assert that the response has any error.
     *
     * @param TestResponse $response
     * @return void
     */
    public function assertHasGraphQLError($response)
    {
        static::assertThat($response->json('errors'), new GraphQLErrorConstraint());
    }

    /**
     * Assert that the response has no errors.
     *
     * @param TestResponse $response
     * @return void
     */
    public function assertHasNoGraphQLError($response)
    {
        static::assertNotNull($response->json('data'));
    }
}