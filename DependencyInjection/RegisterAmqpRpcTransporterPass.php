<?php

namespace Leberknecht\AmqpRpcTransporterBundle\DependencyInjection;

use Leberknecht\AmqpRpcTransporterBundle\Transport\AmqpRpcTransportFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RegisterAmqpRpcTransporterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = new Definition(AmqpRpcTransportFactory::class);
        $definition->addTag('messenger.transport_factory');
        $container->setDefinition('messenger.transport.amqp.rpc.factory', $definition);
    }
}
