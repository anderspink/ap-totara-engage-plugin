<?php

defined('MOODLE_INTERNAL') || die();

/**
 * @param $oldversion
 * @return bool
 */
function xmldb_engage_anderspink_upgrade($oldversion): bool
{
    global $DB, $CFG;
    require_once("{$CFG->dirroot}/container/type/workspace/db/upgradelib.php");

    $dbManager = $DB->get_manager();

    if ($oldversion < 2021041501) {
        $table = new xmldb_table('engage_anderspink_articles');

        $field = new xmldb_field('team', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, 0);
        $index = new xmldb_index('team_idx', false, ['team']);

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
            $dbManager->add_index($table, $index);
        }

        $field = new xmldb_field('api_id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, 0);
        $index = new xmldb_index('apiid_idx', false, ['api_id']);

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
            $dbManager->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2021041501, 'engage', 'anderspink');
    }

    if ($oldversion < 2021041901) {
        $table = new xmldb_table('engage_anderspink_articles');
        $field = new xmldb_field('sync', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, 0);

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2021041901, 'engage', 'anderspink');
    }

    if ($oldversion < 2021042001) {
        $table = new xmldb_table('engage_anderspink_articles');
        $field = new xmldb_field('api_content', XMLDB_TYPE_TEXT);

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2021042001, 'engage', 'anderspink');
    }

    return true;
}
