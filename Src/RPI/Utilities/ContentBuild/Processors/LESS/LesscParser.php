<?php

namespace RPI\Utilities\ContentBuild\Processors\LESS;

class LesscParser extends \lessc_parser
{
    protected function append($prop, $pos = null)
    {
        // add debugging info
        if ($this->lessc->debug) {
            $prop[-2] = $this->sourceName;
            $prop[-3] = $this->line + substr_count(substr($this->buffer, 0, $pos), "\n");
        }

        parent::append($prop, $pos);
    }
}
