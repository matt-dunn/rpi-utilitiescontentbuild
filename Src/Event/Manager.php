<?php

namespace RPI\Utilities\ContentBuild\Event;

class Manager
{
    private static $events = array();

    private function __construct()
    {
    }
    
    /**
     * 
     * @param string $eventName     Name of the event class
     * @param callable $callback
     */
    public static function addEventListener($eventName, $callback)
    {
        if (class_exists($eventName) || interface_exists($eventName)) {
            if (!isset(self::$events[$eventName])) {
                self::$events[$eventName] = array();
            }
            self::$events[$eventName][] = $callback;
        } else {
            throw new \Exception("No event exists for '$eventName'.");
        }
    }

    /**
     * 
     * @param \RPI\Utilities\ContentBuild\Event\IEvent $event
     * @param object $context
     * @return mixed
     */
    public static function fire(\RPI\Utilities\ContentBuild\Event\IEvent $event, $context = null)
    {
        self::fireEvent($event, get_class($event), $context);
        
        $reflection = new \ReflectionClass($event);
        $interfaces = $reflection->getInterfaceNames();
        foreach ($interfaces as $interface) {
            self::fireEvent($event, $interface, $context);
        }
    }
    
    private static function fireEvent(\RPI\Utilities\ContentBuild\Event\IEvent $event, $eventName, $context = null)
    {
        if (isset(self::$events[$eventName])) {
            $eventSource = new \RPI\Utilities\ContentBuild\Event(
                $event->getType(),
                $context,
                $event,
                microtime(true)
            );
            
            foreach (self::$events[$eventName] as $fireEvent) {
                if (is_callable($fireEvent)) {
                    return call_user_func($fireEvent, $eventSource, $event->getParameters());
                }
            }
        }
    }
}
