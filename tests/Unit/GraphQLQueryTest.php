<?php

use Illuminate\Http\UploadedFile;
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
    protected function assertQueryIs($expected, $type, $field, $arguments, $selection)
    {
        $query = new GraphQLQuery($type, $field, $arguments, $selection);

        $this->assertEquals($expected, $query->getQuery());
    }

    protected function assertAssociativeArrayIs(bool $associative, $array)
    {
        $this->assertEquals($associative, GraphQLQuery::isAssociativeArray($array));
    }

    public function testIsAssociativeArray()
    {
        $this->assertAssociativeArrayIs(true, ['a' => 1]);
        $this->assertAssociativeArrayIs(true, ['a' => 1, 'b' => 2]);
        $this->assertAssociativeArrayIs(false, []);
        $this->assertAssociativeArrayIs(false, [1]);
        $this->assertAssociativeArrayIs(false, ['a']);
        $this->assertAssociativeArrayIs(false, [null]);
    }

    public function testEmptyArgumentsEmptySelectionQuery()
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

    public function testOneArgument()
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

    public function testTwoArguments()
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

    public function testNestedArguments()
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

    public function testSequentialArrayArgument()
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

    public function testNullArgument()
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

    public function testBooleanArgument()
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

    public function testNumericArgument()
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

    public function testStringArgument()
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

    public function testEnumArgument()
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

    public function testOneSelection()
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

    public function testTwoSelections()
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

    public function testNestedSelection()
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

    public function testSingleFile()
    {
        $file = UploadedFile::fake()->create('test', 5);
        $query = new GraphQLQuery(
            'mutation',
            'upload',
            ['file' => new Variable('file')],
            []
        );
        $this->assertEquals(
            [
                'operations' => [
                    'query' => 'mutation { upload(file: $file) }',
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

    public function testTwoFiles()
    {
        $file1 = UploadedFile::fake()->create('test', 5);
        $file2 = UploadedFile::fake()->create('test', 5);

        $query = new GraphQLQuery(
            'mutation',
            'upload',
            [
                'file1' => new Variable('file1'),
                'file2' => new Variable('file2')
            ],
            []
        );
        $this->assertEquals(
            [
                'operations' => [
                    'query' => 'mutation { upload(file1: $file1, file2: $file2) }',
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

    public function testNestedFile()
    {
        $file = UploadedFile::fake()->create('test', 5);
        $query = new GraphQLQuery(
            'mutation',
            'upload',
            ['a' => new Variable('a')],
            []
        );
        $this->assertEquals(
            [
                'operations' => [
                    'query' => 'mutation { upload(a: $a) }',
                    'variables' => ['a' => ['file' => null]]
                ],
                'map' => [
                    'a.file' => 0
                ],
                0 => $file
            ],
            $query->getQuery(['a' => ['file' => $file]])
        );
    }

    public function testFileInArray()
    {
        $file = UploadedFile::fake()->create('test', 5);
        $query = new GraphQLQuery(
            'mutation',
            'upload',
            ['a' => [ new Variable('file') ]],
            []
        );
        $this->assertEquals(
            [
                'operations' => [
                    'query' => 'mutation { upload(a: [ $file ]) }',
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
}