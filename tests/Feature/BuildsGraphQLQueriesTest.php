<?php

namespace Tests\Feature;

use Knevelina\LighthouseTest\Traits\BuildsGraphQLQueries;
use Knevelina\LighthouseTest\Schema\Enum;
use Knevelina\LighthouseTest\Schema\GraphQLQuery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Knevelina\LighthouseTest\Traits\BuildsGraphQLQueries
 */
class BuildsGraphQLQueriesTest extends TestCase
{
    use BuildsGraphQLQueries;

    public function testMakesEnums(): void
    {
        $enum = $this->makeEnum('Test');

        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertEquals('Test', $enum->__toString());
    }

    public function testMakesQueries(): void
    {
        $field = 'foo';
        $args = ['arg1' => 'val1'];
        $selection = ['id'];

        $query = $this->makeGraphQLQuery($field, $args, $selection);

        $this->assertInstanceOf(GraphQLQuery::class, $query);

        $this->assertEquals('query', $query->getType());
        $this->assertEquals($field, $query->getField());
        $this->assertEquals($args, $query->getArguments());
        $this->assertEquals($selection, $query->getSelection());
    }

    public function testMakesMutations(): void
    {
        $field = 'foo';
        $args = ['arg1' => 'val1'];
        $selection = ['id'];

        $query = $this->makeGraphQLMutation($field, $args, $selection);

        $this->assertInstanceOf(GraphQLQuery::class, $query);

        $this->assertEquals('mutation', $query->getType());
        $this->assertEquals($field, $query->getField());
        $this->assertEquals($args, $query->getArguments());
        $this->assertEquals($selection, $query->getSelection());
    }
}