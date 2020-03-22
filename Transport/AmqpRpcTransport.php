<?php

namespace leberknecht\AmqpRpcTransporterBundle\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpFactory;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpTransport;
use Symfony\Component\Messenger\Transport\AmqpExt\Connection;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class AmqpRpcTransport extends AmqpTransport implements TransportInterface
{
    private $transceiver = null;

    public function __construct(Connection $connection, SerializerInterface $serializer = null)
    {
        $exchange = new \AMQPExchange($connection->channel());
        $this->transceiver = new AmqpRpcTransceiver($connection, new AmqpFactory(), $exchange, $serializer);
        parent::__construct($connection, $serializer);
    }

    /**
     * {@inheritdoc}
     */
    public function get(): iterable
    {
        return $this->transceiver->get();
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Envelope $envelope): void
    {
        $this->transceiver->ack($envelope);
    }

    /**
     * {@inheritdoc}
     */
    public function send(Envelope $envelope): Envelope
    {
        return $this->transceiver->send($envelope);
    }
}
