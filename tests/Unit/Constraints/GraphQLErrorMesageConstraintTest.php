<?php

namespace Tests\Unit\Constraints;

use Knevelina\LighthouseTest\Constraints\GraphQLErrorMessageConstraint;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Knevelina\LighthouseTest\Constraints\GraphQLErrorMessageConstraint
 */
class GraphQLErrorMessageConstraintTest extends TestCase
{
    public function testReportsNullErrors()
    {
        $constraint = new GraphQLErrorMessageConstraint('Test');

        $this->assertFalse($constraint->matches(null));
        $this->assertEquals(
            'GraphQL error with message "Test" is returned. No error was returned.',
            $constraint->failureDescription(null)
        );
    }

    public function testReportsEmptyErrors()
    {
        $constraint = new GraphQLErrorMessageConstraint('Test');

        $this->assertFalse($constraint->matches([]));
        $this->assertEquals(
            'GraphQL error with message "Test" is returned. No error was returned.',
            $constraint->failureDescription([])
        );
    }

    public function testReportsSingleError()
    {
        $constraint = new GraphQLErrorMessageConstraint('Test');

        $this->assertFalse($constraint->matches([
            ['message' => 'Other']
        ]));
        $this->assertEquals(
            'GraphQL error with message "Test" is returned. The returned errors were Other.',
            $constraint->failureDescription([
                ['message' => 'Other']
            ])
        );
    }

    public function testReportsMultipleErrors()
    {
        $constraint = new GraphQLErrorMessageConstraint('Test');

        $this->assertFalse($constraint->matches([
            ['message' => 'Other'],
            ['message' => 'Another']
        ]));
        $this->assertEquals(
            'GraphQL error with message "Test" is returned. The returned errors were Other, Another.',
            $constraint->failureDescription([
                ['message' => 'Other'],
                ['message' => 'Another']
            ])
        );
    }

    public function testAcceptsSingleError()
    {
        $constraint = new GraphQLErrorMessageConstraint('Test');

        $this->assertTrue($constraint->matches([
            ['message' => 'Test']
        ]));
    }

    public function testAcceptsMultipleErrors()
    {
        $constraint = new GraphQLErrorMessageConstraint('Test');

        $this->assertTrue($constraint->matches([
            ['message' => 'Other'],
            ['message' => 'Test'],
            ['message' => 'Another']
        ]));
    }

    public function testToString()
    {
        $constraint = new GraphQLErrorMessageConstraint('Test');

        $this->assertEquals('GraphQL error with message "Test"', $constraint->toString());
    }
}