<?php

namespace container_anderspink\event;

use container_anderspink\entity\anderspink_bridge;
use core\event\base;

class anderspink_bridge_delete_old_content extends base
{
    protected function init(): void
    {
        $this->data['crud']        = 'c';
        $this->data['edulevel']    = self::LEVEL_TEACHING;
        $this->data['objecttable'] = anderspink_bridge::TABLE;
    }
}