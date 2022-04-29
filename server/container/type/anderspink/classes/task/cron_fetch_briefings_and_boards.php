<?php

namespace container_anderspink\task;

use dml_exception;

class cron_fetch_briefings_and_boards extends \core\task\scheduled_task
{

    public function get_name()
    {
        return get_string('crontask_fetch_briefings_and_boards', 'container_anderspink');
    }

    /**
     * @throws dml_exception
     */
    public function execute()
    {
        global $CFG;

        require_once("{$CFG->dirroot}/container/type/anderspink/lib.php");

        fetch_briefings();
        fetch_boards();
    }
}