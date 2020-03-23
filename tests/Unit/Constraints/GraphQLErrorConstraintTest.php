<?php

namespace Tests\Unit\Constraints;

use Knevelina\LighthouseTest\Constraints\GraphQLErrorConstraint;
use Knevelina\LighthouseTest\Constraints\GraphQLErrorMessageConstraint;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Knevelina\LighthouseTest\Constraints\GraphQLErrorConstraint
 */
class GraphQLErrorConstraintTest extends TestCase
{
    public function testReportsNullError()
    {
        $constraint = new GraphQLErrorConstraint();

        $this->assertFalse($constraint->matches(null));
        $this->assertEquals(
            'GraphQL error is returned. No error was returned.',
            $constraint->failureDescription(null)
        );
    }

    public function testReportsEmptyError()
    {
        $constraint = new GraphQLErrorConstraint();

        $this->assertFalse($constraint->matches([]));
        $this->assertEquals(
            'GraphQL error is returned. Empty errors array was returned.',
            $constraint->failureDescription([])
        );
    }

    public function testAcceptsSingleError()
    {
        $constraint = new GraphQLErrorConstraint();

        $this->assertTrue($constraint->matches([
            ['message' => 'Error']
        ]));
    }

    public function testAcceptsMultipleErrors()
    {
        $constraint = new GraphQLErrorConstraint();

        $this->assertTrue($constraint->matches([
            ['message' => 'Error'],
            ['message' => 'Another error']
        ]));
    }

    public function testToString()
    {
        $constraint = new GraphQLErrorConstraint();

        $this->assertEquals('GraphQL error', $constraint->toString());
    }
}