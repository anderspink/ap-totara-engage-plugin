<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_container_anderspink_install()
{
    global $CFG;
    require_once("{$CFG->dirroot}/container/type/workspace/db/upgradelib.php");
}