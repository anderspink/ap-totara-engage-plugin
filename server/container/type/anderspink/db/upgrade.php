<?php

use core\orm\query\builder;

defined('MOODLE_INTERNAL') || die();

/**
 * @param $oldversion
 *
 * @return bool
 */
function xmldb_container_anderspink_upgrade($oldversion): bool
{
    global $DB, $CFG;
    require_once("{$CFG->dirroot}/container/type/workspace/db/upgradelib.php");

    $dbManager = $DB->get_manager();

    if ($oldversion < 2021030808) {
        $table = new xmldb_table('anderspink_api_settings');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('api_key', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->add_field('team_name', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->add_key('PRIMARY', XMLDB_KEY_PRIMARY, ['id']);

        $table->add_index('team_name_idx', false, ['team_name']);

        if (!$dbManager->table_exists($table)) {
            $dbManager->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2021030808, 'container', 'anderspink');
    }

    if ($oldversion < 2021031101) {
        $table = new xmldb_table('anderspink_briefings');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('team', XMLDB_TYPE_INTEGER, '10', 10, XMLDB_NOTNULL);
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->add_field('type', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->add_field('api_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('img', XMLDB_TYPE_TEXT);

        $table->add_key('PRIMARY', XMLDB_KEY_PRIMARY, ['id']);

        $table->add_index('team_idx', false, ['team']);

        if (!$dbManager->table_exists($table)) {
            $dbManager->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2021031101, 'container', 'anderspink');
    }

    if ($oldversion < 2021031801) {
        $table = new xmldb_table('anderspink_boards');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('team', XMLDB_TYPE_INTEGER, '10', 10, XMLDB_NOTNULL);
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->add_field('type', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->add_field('api_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('img', XMLDB_TYPE_TEXT);

        $table->add_key('PRIMARY', XMLDB_KEY_PRIMARY, ['id']);

        $table->add_index('team_idx', false, ['team']);

        if (!$dbManager->table_exists($table)) {
            $dbManager->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2021031801, 'container', 'anderspink');
    }

    if ($oldversion < 2021042101) {
        $table = new xmldb_table('anderspink_bridge');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('team', XMLDB_TYPE_INTEGER, '10', 10, XMLDB_NOTNULL);
        $table->add_field('workspace', XMLDB_TYPE_INTEGER, '10', 10, XMLDB_NOTNULL);
        $table->add_field('type', XMLDB_TYPE_CHAR, '10', 10, XMLDB_NOTNULL);
        $table->add_field('type_id', XMLDB_TYPE_INTEGER, '10', 10, XMLDB_NOTNULL);

        $table->add_key('PRIMARY', XMLDB_KEY_PRIMARY, ['id']);

        $table->add_index('team_idx', false, ['team']);
        $table->add_index('workspace_idx', false, ['workspace']);
        $table->add_index('type_idx', false, ['type_id']);

        if (!$dbManager->table_exists($table)) {
            $dbManager->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2021042101, 'container', 'anderspink');
    }

    if ($oldversion < 2021042102) {
        $table = new xmldb_table('anderspink_bridge');
        $field = new xmldb_field('user', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, 0);

        $index = new xmldb_index('user_idx', false, ['user']);

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
            $dbManager->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2021042102, 'container', 'anderspink');
    }

    if ($oldversion < 2021042605) {
        upgrade_plugin_savepoint(true, 2021042605, 'container', 'anderspink');
    }

    if ($oldversion < 2021043002) {
        $table = new xmldb_table('anderspink_bridged_articles');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('bridge', XMLDB_TYPE_INTEGER, '10', 10, XMLDB_NOTNULL);
        $table->add_field('article', XMLDB_TYPE_INTEGER, '10', 10, XMLDB_NOTNULL);

        $table->add_key('PRIMARY', XMLDB_KEY_PRIMARY, ['id']);

        $table->add_index('bridge_idx', false, ['bridge']);
        $table->add_index('article_idx', false, ['article']);

        if (!$dbManager->table_exists($table)) {
            $dbManager->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2021043002, 'container', 'anderspink');
    }

    if ($oldversion < 2021043003) {
        upgrade_plugin_savepoint(true, 2021043003, 'container', 'anderspink');
    }

    if ($oldversion < 2021050701) {
        upgrade_plugin_savepoint(true, 2021050701, 'container', 'anderspink');
    }

    if ($oldversion < 2021050702) {
        upgrade_plugin_savepoint(true, 2021050702, 'container', 'anderspink');
    }

    if ($oldversion < 2022012601) {
        $table = new xmldb_table('anderspink_discussion_index');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('discussion', XMLDB_TYPE_INTEGER, '10', 10, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->add_key('PRIMARY', XMLDB_KEY_PRIMARY, ['id']);

        $table->add_index('discussion_idx', false, ['discussion']);

        if (!$dbManager->table_exists($table)) {
            $dbManager->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2022012601, 'container', 'anderspink');
    }

    if ($oldversion < 2022033101) {
        $config = builder::table('config')->where('name', 'anderspink_discussion_post_user')->one();

        if ($config->value < 0) {
            $config->value = 0;
            builder::table('config')->update_record($config);
        }

        upgrade_plugin_savepoint(true, 2022033101, 'container', 'anderspink');
    }

    return true;
}