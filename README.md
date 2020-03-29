# About
A workaround bundle to bring RPC functionality to the AMQP transporter of the Symfony messenger component

## What it does
This bundle introduces `ampqp-rpc` transporter, which is identical to the normal `amqp` transporter, except that it will use `reply_to` and `correlation_id` headers. On receive, the content of the `HandledStamp` will be published to the queue identified in the `reply_to` field. On send, a random queue name will be generated and after publishing the original message, we will wait for a response on that queue, then adding `ResponseStamp` with the result to the envelope. 

## Installation
```bash
composer require leberknecht/amqp-rpc-transporter-bundle
```

## Usage

```yaml
# messenger.yaml
framework:
    messenger:
        transports:
            rpc_calls:
                dsn: 'amqp-rpc://user:password@rabbit-mq-host/%2f/rpc_calls'
        routing:
            # Route your messages to the transports
            'App\Message\RpcCallMessage': rpc_calls
```

Then use it like this:
```php
    // send
    $rpcCallMessage = new RpcCallMessage();

    $envelope = $this->messageBus->dispatch($rpcCallMessage);
    /** @var ResponseStamp $response */
    $response = $envelope->last(ResponseStamp::class);
    $result = $response->getResult();
```

To set the result from the handler, just return something:
```php
    final public function __invoke(RpcCallMessage $message): void
    {
        [...]
        return 42;
    }
```

## Remarks
This is a work-in-progress, as a first-shot workaround. It would be much more elegant to override the `messenger.transport.amqp.factory` service and add `rpc: true` and `rpc_queue_name` to the messenger config, so we extend the existing transporter instead of bringing in this new one. Also note: in this state, we will always generate a exclusive queue with a random name for the response. This is sub-optimal for heavy loaded queues, see https://www.rabbitmq.com/tutorials/tutorial-six-python.html  