<?php

namespace leberknecht\AmqpRpcTransporterBundle\Transport;

use AMQPExchange;
use leberknecht\AmqpRpcTransporterBundle\Stamp\ResponseStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpFactory;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpReceivedStamp;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpReceiver;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpSender;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpStamp;
use Symfony\Component\Messenger\Transport\AmqpExt\Connection;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class AmqpRpcTransceiver extends AmqpReceiver
{
    /**
     * @var PhpSerializer|SerializerInterface
     */
    private $serializer;
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var AmqpFactory
     */
    private $amqpFactory;
    /**
     * @var AmqpSender
     */
    private $amqpSender;
    /**
     * @var AMQPExchange
     */
    private $exchange;

    public function __construct(Connection $connection, AmqpFactory $amqpFactory, AMQPExchange $exchange, SerializerInterface $serializer = null)
    {
        $this->connection = $connection;
        $this->serializer = $serializer ?? new PhpSerializer();
        $this->amqpFactory = $amqpFactory;
        $this->exchange = $exchange;
        $this->amqpSender = new AmqpSender($connection, $serializer);
        parent::__construct($connection, $serializer);
    }

    public function ack(Envelope $envelope): void
    {
        try {
            $stamp = $this->findAmqpStamp($envelope);
            $resultStamp = $this->findHandledStamp($envelope);
            $this->exchange->publish($resultStamp->getResult(), $stamp->getAmqpEnvelope()->getReplyTo(), AMQP_NOPARAM, [
                'correlation_id' => $stamp->getAmqpEnvelope()->getCorrelationId()
            ]);
        } catch (\AMQPException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }
    }

    public function send(Envelope $envelope, $correlationId)
    {
        $responseQueue = $this->createResponseQueue();
        $envelope = $envelope->with(AmqpStamp::createWithAttributes([
            'reply_to' => $responseQueue->getName(),
            'correlation_id' => $correlationId
        ], $envelope->last(AmqpStamp::class)));

        $this->amqpSender->send($envelope);

        do {
            $response = $responseQueue->get();
            usleep(20000);
        } while(!$response or $response->getCorrelationId() != $correlationId);
        $envelope = $envelope->with(new ResponseStamp($response->getBody()));

        return $envelope;
    }

    private function findHandledStamp(Envelope $envelope): ?HandledStamp
    {
        $resultStamp = $envelope->last(HandledStamp::class);
        assert($resultStamp instanceof HandledStamp || null);

        return $resultStamp;
    }


    private function findAmqpStamp(Envelope $envelope): AmqpReceivedStamp
    {
        $amqpReceivedStamp = $envelope->last(AmqpReceivedStamp::class);
        assert($amqpReceivedStamp instanceof AmqpReceivedStamp || null);
        if (null === $amqpReceivedStamp) {
            throw new LogicException('No "AmqpReceivedStamp" stamp found on the Envelope.');
        }

        return $amqpReceivedStamp;
    }

    /**
     * @return \AMQPQueue
     *
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPException
     */
    private function createResponseQueue(): \AMQPQueue
    {
        $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $responseQueueName = sprintf(
            'rpc-amqp.gen-%s', substr(str_shuffle(str_repeat($alphabet, ceil(20 / strlen($alphabet)))), 1, 20)
        );
        $receiveQueue = $this->amqpFactory->createQueue($this->connection->channel());
        $receiveQueue->setFlags(AMQP_EXCLUSIVE);
        $receiveQueue->setName($responseQueueName);
        $receiveQueue->declare();

        return $receiveQueue;
    }
}
