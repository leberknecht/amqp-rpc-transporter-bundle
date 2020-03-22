<?php

namespace leberknecht\AmqpRpcTransporterBundle\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class ResponseStamp implements StampInterface
{
    private $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }
}
