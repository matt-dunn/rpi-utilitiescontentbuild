<?php

namespace RPI\Utilities\ContentBuild;

class Event
{
    public $type = null;
    public $target = null;
    public $srcEvent = null;
    public $timestamp = null;
    
    public function __construct($type, $target, \RPI\Utilities\ContentBuild\Event\IEvent $srcEvent, $timestamp)
    {
        $this->type = $type;
        $this->target = $target;
        $this->srcEvent = $srcEvent;
        $this->timestamp = $timestamp;
    }
}
