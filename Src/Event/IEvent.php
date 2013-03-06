<?php

namespace RPI\Utilities\ContentBuild\Event;

interface IEvent
{
    /**
     * @return string Name of the event. This will normally be the lowercase name of the
     *                class (without the namespace) followed by some namespace
     *                e.g. "viewupdated.RPI".
     */
    public function getType();
    
    /**
     * @return array
     */
    public function getParameters();
    
    public function setReturnValue($value);
    
    public function getReturnValue();
}
