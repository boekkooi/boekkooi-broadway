<?php
namespace Boekkooi\Broadway\Bundle;

use Boekkooi\Broadway\Bundle\DependencyInjection\Compiler\EventHandlerMapPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BoekkooiBroadwayBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EventHandlerMapPass());
    }
}
