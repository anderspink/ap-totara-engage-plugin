<?php

namespace container_anderspink\event;

use coding_exception;
use container_anderspink\entity\anderspink_bridge;
use core\event\base;

class anderspink_bridge_created extends base
{

    protected function init(): void
    {
        $this->data['crud']        = 'c';
        $this->data['edulevel']    = self::LEVEL_TEACHING;
        $this->data['objecttable'] = anderspink_bridge::TABLE;
    }
}