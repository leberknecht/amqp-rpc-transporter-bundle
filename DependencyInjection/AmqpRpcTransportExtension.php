<?php

namespace DH\DoctrineAuditBundle\DependencyInjection;

use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Leberknecht\AmqpRpcTransporterBundle\Transport\AmqpRpcTransportFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Messenger\Transport\TransportFactory;

class AmqpRpcTransportExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $definition = new Definition(AmqpRpcTransportFactory::class);
        $definition->addTag('messenger.transport_factory');
        $container->setDefinition('messenger.transport.amqp-rpc.factory', $definition);
    }
}

