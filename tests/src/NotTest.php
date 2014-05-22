<?php

namespace CL\LunaJsonStore\Test;

use CL\LunaJsonStore\Not;

class NotTest extends AbstractTestCase
{
    /**
     * @covers CL\LunaJsonStore\Not::__construct
     * @covers CL\LunaJsonStore\Not::getValue
     */
    public function testConstruct()
    {
        $not = new Not('some val');

        $this->assertSame('some val', $not->getValue());
    }
}
