<?php

namespace Tests\Stamp;

use leberknecht\AmqpRpcTransporterBundle\Stamp\ResponseStamp;
use PHPUnit\Framework\TestCase;

class ResponseStampTest extends TestCase
{
    public function testStamp()
    {
        $responseStamp = new ResponseStamp(42);
        $this->assertEquals(42, $responseStamp->getResult());
    }
}