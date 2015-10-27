<?php
namespace Boekkooi\Broadway\EventHandling\Locator;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerHandlerLocator implements HandlerLocator
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var array
     */
    private $events = [];

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Register a event handler service with the events that it handles.
     *
     * @param string $serviceId
     * @param string[] $events
     */
    public function registerEventHandlerService($serviceId, array $events)
    {
        foreach ($events as $eventName) {
            if (!array_key_exists($eventName, $this->events)) {
                $this->events[$eventName] = [];
            }

            $this->events[$eventName][] = $serviceId;
        }
    }

    /**
     * @inheritdoc
     */
    public function getHandlersForEvent($eventName)
    {
        if (!array_key_exists($eventName, $this->events)) {
            return [];
        }

        return array_map(
            [ $this->container, 'get' ],
            $this->events[$eventName]
        );
    }
}
