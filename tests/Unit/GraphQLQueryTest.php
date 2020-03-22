<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use Knevelina\LighthouseTest\Schema\Enum;
use Knevelina\LighthouseTest\Schema\GraphQLQuery;
use Knevelina\LighthouseTest\Schema\Variable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Knevelina\LighthouseTest\Schema\GraphQLQuery
 * @uses \Knevelina\LighthouseTest\Schema\Enum
 */
class GraphQLQueryTest extends TestCase
{
    protected function assertQueryIs($expected, $type, $field, $arguments, $selection): void
    {
        $query = new GraphQLQuery($type, $field, $arguments, $selection);

        $this->assertEquals($expected, $query->getQuery());
    }

    protected function assertAssociativeArrayIs(bool $associative, $array): void
    {
        $this->assertEquals($associative, GraphQLQuery::isAssociativeArray($array));
    }

    public function testIsAssociativeArray(): void
    {
        $this->assertAssociativeArrayIs(true, ['a' => 1]);
        $this->assertAssociativeArrayIs(true, ['a' => 1, 'b' => 2]);
        $this->assertAssociativeArrayIs(false, []);
        $this->assertAssociativeArrayIs(false, [1]);
        $this->assertAssociativeArrayIs(false, ['a']);
        $this->assertAssociativeArrayIs(false, [null]);
    }

    public function testEmptyArgumentsEmptySelectionQuery(): void
    {
        $this->assertQueryIs(
            [
                'query' => 'query { test }',
                'variables' => []
            ],
            'query',
            'test',
            [],
            []
        );
    }

    public function testOneArgument(): void
    {
        $this->assertQueryIs(
            [
                'query' => 'query { test(a: 1) }',
                'variables' => []
            ],
            'query',
            'test',
            ['a' => 1],
            []
        );
    }

    public function testTwoArguments(): void
    {
        $this->assertQueryIs(
            [
                'query' => 'query { test(a: 1, b: 2) }',
                'variables' => []
            ],
            'query',
            'test',
            ['a' => 1, 'b' => 2],
            []
        );
    }

    public function testNestedArguments(): void
    {
        $this->assertQueryIs(
            [
                'query' => 'query { test(a: 1, b: { c: 2 }) }',
                'variables' => []
            ],
            'query',
            'test',
            ['a' => 1, 'b' => ['c' => 2]],
            []
        );
    }

    public function testSequentialArrayArgument(): void
    {
        $this->assertQueryIs(
            [
                'query' => 'query { test(a: [ 1, 2 ]) }',
                'variables' => []
            ],
            'query',
            'test',
            ['a' => [1, 2]],
            []
        );
    }

    public function testNullArgument(): void
    {
        $this->assertQueryIs(
            [
                'query' => 'query { test(a: null) }',
                'variables' => []
            ],
            'query',
            'test',
            ['a' => null],
            []
        );
    }

    public function testBooleanArgument(): void
    {
        $this->assertQueryIs(
            [
                'query' => 'query { test(a: true) }',
                'variables' => []
            ],
            'query',
            'test',
            ['a' => true],
            []
        );
    }

    public function testNumericArgument(): void
    {
        $this->assertQueryIs(
            [
                'query' => 'query { test(a: 3) }',
                'variables' => []
            ],
            'query',
            'test',
            ['a' => 3],
            []
        );
    }

    public function testStringArgument(): void
    {
        $this->assertQueryIs(
            [
                'query' => 'query { test(a: "test") }',
                'variables' => []
            ],
            'query',
            'test',
            ['a' => 'test'],
            []
        );
    }

    public function testEnumArgument(): void
    {
        $this->assertQueryIs(
            [
                'query' => 'query { test(a: TEST) }',
                'variables' => []
            ],
            'query',
            'test',
            ['a' => new Enum('TEST')],
            []
        );
    }

    public function testOneSelection(): void
    {
        $this->assertQueryIs(
            [
                'query' => 'query { test(a: 1){ id } }',
                'variables' => []
            ],
            'query',
            'test',
            ['a' => 1],
            ['id']
        );
    }

    public function testTwoSelections(): void
    {
        $this->assertQueryIs(
            [
                'query' => "query { test(a: 1){ id\nname } }",
                'variables' => []
            ],
            'query',
            'test',
            ['a' => 1],
            ['id', 'name']
        );
    }

    public function testNestedSelection(): void
    {
        $this->assertQueryIs(
            [
                'query' => "query { test(a: 1){ id\nuser { id } } }",
                'variables' => []
            ],
            'query',
            'test',
            ['a' => 1],
            ['id', 'user' => ['id']]
        );
    }

    public function testSingleFile(): void
    {
        $file = UploadedFile::fake()->create('test', 5);
        $query = new GraphQLQuery(
            'mutation',
            'upload',
            ['file' => new Variable('file', 'Upload!')],
            []
        );
        $this->assertEquals(
            [
                'operations' => [
                    'query' => 'mutation($file: Upload!) { upload(file: $file) }',
                    'variables' => ['file' => null]
                ],
                'map' => [
                    'file' => 0
                ],
                0 => $file
            ],
            $query->getQuery(['file' => $file])
        );
    }

    public function testTwoFiles(): void
    {
        $file1 = UploadedFile::fake()->create('test', 5);
        $file2 = UploadedFile::fake()->create('test', 5);

        $query = new GraphQLQuery(
            'mutation',
            'upload',
            [
                'file1' => new Variable('file1', 'Upload!'),
                'file2' => new Variable('file2', 'Upload!')
            ],
            []
        );
        $this->assertEquals(
            [
                'operations' => [
                    'query' => 'mutation($file1: Upload!, $file2: Upload!) { upload(file1: $file1, file2: $file2) }',
                    'variables' => ['file1' => null, 'file2' => null]
                ],
                'map' => [
                    'file1' => 0,
                    'file2' => 1
                ],
                0 => $file1,
                1 => $file2
            ],
            $query->getQuery(['file1' => $file1, 'file2' => $file2])
        );
    }

    public function testNestedFile(): void
    {
        $file = UploadedFile::fake()->create('test', 5);
        $query = new GraphQLQuery(
            'mutation',
            'upload',
            ['input' => new Variable('input', 'FileInput!')],
            []
        );
        $this->assertEquals(
            [
                'operations' => [
                    'query' => 'mutation($input: FileInput!) { upload(input: $input) }',
                    'variables' => ['input' => ['file' => null]]
                ],
                'map' => [
                    'input.file' => 0
                ],
                0 => $file
            ],
            $query->getQuery(['input' => ['file' => $file]])
        );
    }

    public function testFileInArray(): void
    {
        $file = UploadedFile::fake()->create('test', 5);
        $query = new GraphQLQuery(
            'mutation',
            'upload',
            ['input' => [ new Variable('file', 'Upload!') ]],
            []
        );
        $this->assertEquals(
            [
                'operations' => [
                    'query' => 'mutation($file: Upload!) { upload(input: [ $file ]) }',
                    'variables' => ['file' => null]
                ],
                'map' => [
                    'file' => 0
                ],
                0 => $file
            ],
            $query->getQuery(['file' => $file])
        );
    }

    public function testValidTypeParam(): void
    {
        $query = new GraphQLQuery('query', 'foo', [], []);
        $this->assertInstanceOf(GraphQLQuery::class, $query);

        $mutation = new GraphQLQuery('mutation', 'foo', [], []);
        $this->assertInstanceOf(GraphQLQuery::class, $mutation);
    }

    public function testInvalidTypeParam(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GraphQLQuery('foo', 'foo', [], []);
    }

    public function testGetters(): void
    {
        $type = 'query';
        $field = 'foo';
        $args = ['id' => 1];
        $selection = ['id'];

        $query = new GraphQLQuery($type, $field, $args, $selection);

        $this->assertEquals($type, $query->getType());
        $this->assertEquals($field, $query->getField());
        $this->assertEquals($args, $query->getArguments());
        $this->assertEquals($selection, $query->getSelection());
    }
}