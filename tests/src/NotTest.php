<?php

namespace Harp\JsonStore\Test;

use Harp\JsonStore\Not;

/**
 * @coversDefaultClass Harp\JsonStore\Not
 */
class NotTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getValue
     */
    public function testConstruct()
    {
        $not = new Not('some val');

        $this->assertSame('some val', $not->getValue());
    }
}
