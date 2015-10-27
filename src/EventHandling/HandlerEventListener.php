<?php
namespace Boekkooi\Broadway\EventHandling;

use Boekkooi\Broadway\EventHandling\EventNameExtractor\EventNameExtractor;
use Boekkooi\Broadway\EventHandling\Exception\CanNotInvokeHandlerException;
use Boekkooi\Broadway\EventHandling\Locator\HandlerLocator;
use Boekkooi\Broadway\EventHandling\MethodNameInflector\MethodNameInflector;
use Broadway\Domain\DomainMessage;
use Broadway\EventHandling\EventListenerInterface;

class HandlerEventListener implements EventListenerInterface
{
    /**
     * @var EventNameExtractor
     */
    private $nameExtractor;
    /**
     * @var MethodNameInflector
     */
    private $methodNameInflector;
    /**
     * @var HandlerLocator
     */
    private $handlerLocator;

    public function __construct(EventNameExtractor $nameExtractor, HandlerLocator $handlerLocator, MethodNameInflector $methodNameInflector)
    {
        $this->nameExtractor = $nameExtractor;
        $this->methodNameInflector = $methodNameInflector;
        $this->handlerLocator = $handlerLocator;
    }

    /**
     * @param DomainMessage $domainMessage
     * @return void
     */
    public function handle(DomainMessage $domainMessage)
    {
        $event = $domainMessage->getPayload();
        $eventName = $this->nameExtractor->extract($event);
        $eventHandlers = $this->handlerLocator->getHandlersForEvent($eventName);

        foreach ($eventHandlers as $handler) {
            $methodName = $this->methodNameInflector->inflect($event, $handler);

            // is_callable is used here instead of method_exists, as method_exists
            // will fail when given a handler that relies on __call.
            if (!is_callable([$handler, $methodName])) {
                throw CanNotInvokeHandlerException::forEvent(
                    $event,
                    sprintf("Method '%s' does not exist on handler", is_object($methodName) ? get_class($methodName) : gettype($methodName))
                );
            }

            $handler->{$methodName}($event, $domainMessage);
        }
    }
}
