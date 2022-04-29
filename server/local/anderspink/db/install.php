<?php

defined('MOODLE_INTERNAL') || die();

/**
 * @return bool
 * @throws Throwable
 * @throws coding_exception
 * @throws dml_transaction_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_local_anderspink_install()
{
    global $CFG, $DB;

    $dbManager   = $DB->get_manager();
    $transaction = $DB->start_delegated_transaction();

    try {
        $roleId = create_role('Workspace Discussion Author', 'workspacediscussionauthor', '', 'user');

        $DB->insert_record(
            'role_capabilities',
            (object)[
                'contextid'    => 1,
                'roleid'       => $roleId,
                'capability'   => 'container/workspace:discussioncreate',
                'permission'   => 1,
                'timemodified' => time(),
                'modifierid'   => 0,
            ]
        );

        $DB->insert_record(
            'role_context_levels',
            (object)[
                'roleid'       => $roleId,
                'contextlevel' => (context_system::instance())->contextlevel,
            ]
        );
    } catch (Exception $e) {
        $DB->rollback_delegated_transaction($DB);
    }

    upgrade_plugin_savepoint(true, 2022031701, 'local', 'anderspink');
    $DB->commit_delegated_transaction($transaction);

    return true;
}
