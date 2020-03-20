<?php

namespace Tests\Unit;

use Knevelina\LighthouseTest\Schema\Enum;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Knevelina\LighthouseTest\Schema\Enum
 */
class EnumTest extends TestCase
{
    public function testToString()
    {
        $enum = new Enum('Test');

        $this->assertEquals('Test', $enum->__toString());
    }
}