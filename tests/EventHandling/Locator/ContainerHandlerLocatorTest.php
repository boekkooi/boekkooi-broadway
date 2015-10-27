<?php
namespace Tests\Boekkooi\Broadway\EventHandling\Locator;

use Boekkooi\Broadway\EventHandling\Locator\ContainerHandlerLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerHandlerLocator
     */
    private $locator;
    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $containerMock;

    public function setUp()
    {
        $this->containerMock = $this->getMock(ContainerInterface::class);

        $this->locator = new ContainerHandlerLocator($this->containerMock);
    }

    public function testASetOfEventHandlersCanBeRetrieved()
    {
        $service1 = 'serviceInstance1';
        $service2 = 'serviceInstance2';

        $this->locator->registerEventHandlerService('service1', ['EventA', 'EventB']);
        $this->locator->registerEventHandlerService('service2', ['EventB', 'EventC']);

        $this->containerMock
            ->expects(self::any())
            ->method('get')
            ->willReturnMap([
                [ 'service1', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $service1 ],
                [ 'service2', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $service2 ]
            ]);

        self::assertEquals([ $service1 ] , $this->locator->getHandlersForEvent('EventA'));
        self::assertEquals([ $service1, $service2 ] , $this->locator->getHandlersForEvent('EventB'));
        self::assertEquals([ $service2 ] , $this->locator->getHandlersForEvent('EventC'));
        self::assertEquals([ ] , $this->locator->getHandlersForEvent('EventNone'));
    }
}
