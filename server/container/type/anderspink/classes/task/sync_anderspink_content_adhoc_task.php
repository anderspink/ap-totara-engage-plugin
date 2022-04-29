<?php

namespace container_anderspink\task;

use coding_exception;
use container_anderspink\bridge;
use container_anderspink\event\anderspink_bridge_created;
use container_workspace\exception\discussion_exception;
use core\task\adhoc_task;

class sync_anderspink_content_adhoc_task extends adhoc_task
{
    /**
     * @throws coding_exception
     */
    public function execute()
    {
        $data          = (array) $this->get_custom_data();
        $data['other'] = (array) $data['other'];

        $event = anderspink_bridge_created::create($data);

        $event->trigger();
    }
}