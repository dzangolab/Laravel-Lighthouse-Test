<?php

namespace Tests\Unit;

use Knevelina\LighthouseTest\Schema\Variable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Knevelina\LighthouseTest\Schema\Variable
 */
class VariableTest extends TestCase
{
    public function testToString()
    {
        $enum = new Variable('test');

        $this->assertEquals('$test', $enum->__toString());
    }
}