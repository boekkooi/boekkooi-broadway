<?php
namespace Tests\Boekkooi\Broadway\EventHandling;

use Boekkooi\Broadway\EventHandling\EventNameExtractor\EventNameExtractor;
use Boekkooi\Broadway\EventHandling\Exception\CanNotInvokeHandlerException;
use Boekkooi\Broadway\EventHandling\HandlerEventListener;
use Boekkooi\Broadway\EventHandling\Locator\HandlerLocator;
use Boekkooi\Broadway\EventHandling\MethodNameInflector\MethodNameInflector;
use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Tests\Boekkooi\Broadway\Fixtures\Event\FinishedEvent;

class HandlerEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventNameExtractor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nameExtractor;
    /**
     * @var MethodNameInflector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $methodNameInflector;
    /**
     * @var HandlerLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $handlerLocator;
    /**
     * @var HandlerEventListener
     */
    private $eventListener;

    public function setUp()
    {
        $this->nameExtractor = $this->getMock(EventNameExtractor::class);
        $this->methodNameInflector = $this->getMock(MethodNameInflector::class);
        $this->handlerLocator = $this->getMock(HandlerLocator::class);

        $this->eventListener = new HandlerEventListener($this->nameExtractor, $this->handlerLocator, $this->methodNameInflector);
    }

    public function testHandleWillForwardToASetOfHandlers()
    {
        $event = new FinishedEvent();
        $domainMessage = new DomainMessage(12, 1, new Metadata(), $event, DateTime::now());

        $eventHandler1Mock = $this->getMock(\stdClass::class, array('handleFinishedEvent'));
        $eventHandler1Mock
            ->expects(self::once())
            ->method('handleFinishedEvent')
            ->with($event, $domainMessage);

        $eventHandler2Mock = $this->getMock(\stdClass::class, array('handle'));
        $eventHandler2Mock
            ->expects(self::once())
            ->method('handle')
            ->with($event, $domainMessage);

        $this->nameExtractor
            ->expects(self::atLeastOnce())
            ->method('extract')
            ->with($event)
            ->willReturn('Finished');

        $this->handlerLocator
            ->expects(self::atLeastOnce())
            ->method('getHandlersForEvent')
            ->with('Finished')
            ->willReturn([ $eventHandler1Mock, $eventHandler2Mock ]);

        $this->methodNameInflector
            ->expects(self::any())
            ->method('inflect')
            ->willReturnMap([
                [$event, $eventHandler1Mock, 'handleFinishedEvent'],
                [$event, $eventHandler2Mock, 'handle']
            ]);

        $this->eventListener->handle($domainMessage);
    }

    public function testHandleThrowAExceptionWhenAHandlerHasNoEventMethod()
    {
        $this->setExpectedException(CanNotInvokeHandlerException::class);

        $event = new \stdClass();
        $domainMessage = new DomainMessage(12, 1, new Metadata(), $event, DateTime::now());

        $badHandler = new \stdClass();

        $this->nameExtractor
            ->expects(self::atLeastOnce())
            ->method('extract')
            ->with($event)
            ->willReturn('Finished');

        $this->handlerLocator
            ->expects(self::atLeastOnce())
            ->method('getHandlersForEvent')
            ->with('Finished')
            ->willReturn([ $badHandler ]);

        $this->methodNameInflector
            ->expects(self::any())
            ->method('inflect')
            ->with($event, $badHandler)
            ->willReturn('aEventMethod');

        $this->eventListener->handle($domainMessage);
    }
}
