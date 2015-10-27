<?php
namespace Tests\Boekkooi\Broadway\Bundle;

use Boekkooi\Broadway\Bundle\BoekkooiBroadwayBundle;
use Boekkooi\Broadway\Bundle\DependencyInjection\Compiler\EventHandlerMapPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BoekkooiBroadwayBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BoekkooiBroadwayBundle
     */
    private $bundle;

    protected function setUp()
    {
        $this->bundle = new BoekkooiBroadwayBundle();
    }


    public function testBuildRegistersCompilerPasses()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder $container */
        $container = $this->getMock(ContainerBuilder::class, [ 'addCompilerPass' ]);
        $container
            ->expects(self::once())
            ->method('addCompilerPass')
            ->with(self::isInstanceOf(EventHandlerMapPass::class));

        $this->bundle->build($container);
    }
}
