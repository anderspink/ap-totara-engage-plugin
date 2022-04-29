<?php

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @param int $oldversion
 * @param object $block
 * @return bool
 */
function xmldb_block_workspaces_upgrade($oldversion, $block)
{
    global $CFG, $DB;

    $dbmanager = $DB->get_manager();

    if ($oldversion < 2021050601) {
        upgrade_plugin_savepoint(true, 2021050601, 'block', 'workspaces');
    }

    return true;
}
