<?php

namespace Tests\Unit;

use Knevelina\LighthouseTest\Schema\Variable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Knevelina\LighthouseTest\Schema\Variable
 */
class VariableTest extends TestCase
{
    public function testGetName()
    {
        $var = new Variable('test', 'String');
        
        $this->assertEquals('test', $var->getName());
    }

    public function testGetType()
    {
        $var = new Variable('test', 'String');

        $this->assertEquals('String', $var->getType());
    }

    public function testToString()
    {
        $var = new Variable('test', 'String');

        $this->assertEquals('$test', $var->__toString());
    }
}