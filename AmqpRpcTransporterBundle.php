<?php

namespace Leberknecht\AmqpRpcTransporterBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Leberknecht\AmqpRpcTransporterBundle\DependencyInjection\RegisterAmqpRpcTransporterPass;

class AmqpRpcTransporterBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterAmqpRpcTransporterPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
