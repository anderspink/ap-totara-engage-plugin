<?php

namespace container_anderspink\task;

use coding_exception;
use container_anderspink\anderspink;
use container_anderspink\board;
use container_anderspink\briefing;
use container_anderspink\entity\anderspink_bridge;
use container_anderspink\event\anderspink_bridge_created;
use container_anderspink\local\anderspink_helper;
use container_workspace\workspace;
use core\task\scheduled_task;
use dml_exception;

class cron_sync_briefings extends scheduled_task
{
    public function get_name()
    {
        return get_string('crontask_sync_briefings_and_boards', 'container_anderspink');
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function execute()
    {
        global $DB;

        $records = $DB->get_records(anderspink_bridge::TABLE);

        foreach ($records as $record) {
            $workspace = workspace::from_id($record->workspace);
            $team      = anderspink::from_id($record->team);

            /** @var briefing|board $class */
            $class    = anderspink_helper::RESOURCETYPE[$record->type];
            $resource = $class::from_apiid($record->type_id);

            $eventData = [
                'objectid'  => $record->id,
                'contextid' => $workspace->get_context()->id,
                'other'     => [
                    'user'         => $record->user,
                    'apiid'        => $resource->apiid,
                    'endpoint'     => $resource::ENDPOINT,
                    'teamid'       => $team->get_id(),
                    'resourcename' => $resource->name,
                    'workspaceid'  => $workspace->get_id(),
                    'sync'         => true
                ],
            ];

            $event = anderspink_bridge_created::create($eventData);
            $event->trigger();
        }
    }
}