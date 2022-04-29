<?php

namespace container_anderspink\local;

require_once("{$CFG->dirroot}/container/type/anderspink/lib.php");

use coding_exception;
use container_anderspink\anderspink;
use container_anderspink\board;
use container_anderspink\briefing;
use container_anderspink\entity\anderspink_api;
use container_anderspink\entity\anderspink_boards;
use container_anderspink\entity\anderspink_bridge;
use container_anderspink\entity\anderspink_briefings;
use container_anderspink\task\sync_anderspink_content_adhoc_task;
use container_workspace\workspace;
use core\task\manager;
use dml_exception;

final class anderspink_helper
{
    const RESOURCETYPE = [
        'board'    => board::class,
        'briefing' => briefing::class,
    ];

    /**
     * @param  string  $teamName
     * @param  string  $apiKey
     *
     * @return false|object
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function add_api(string $teamName, string $apiKey)
    {
        if (!$validate = self::validateApiKey($apiKey)) {
            throw new coding_exception("Could not validate API correctly");
        }

        $currentTime = time();

        $record               = new anderspink_api();
        $record->api_key      = $apiKey;
        $record->team_name    = $teamName;
        $record->timecreated  = $currentTime;
        $record->timemodified = $currentTime;

        $record->save();

        fetch_briefings();
        fetch_boards();

        return anderspink::from_id($record->id);
    }

    /**
     * @param  int  $apiId
     * @param  string  $teamName
     *
     * @return false|object
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function edit_api(int $apiId, string $teamName)
    {
        $record               = anderspink_api::repository()->find($apiId);
        $record->team_name    = $teamName;
        $record->timemodified = time();

        $record->save();

        return anderspink::from_id($apiId);
    }

    /**
     * @param  int  $apiId
     *
     * @return bool
     * @throws coding_exception
     */
    public static function delete_api(int $apiId): bool
    {
        $api = anderspink_api::repository()->find($apiId);

        if ($api->exists()) {
            $api->delete();

            anderspink_boards::repository()->where('team', $apiId)->delete();
            anderspink_briefings::repository()->where('team', $apiId)->delete();
            anderspink_bridge::repository()->where('team', $apiId)->delete();
        }

        return true;
    }

    /**
     * @param  int  $workspaceId
     * @param  int  $teamId
     * @param  string  $bridgeType
     * @param  int  $bridgeId
     *
     * @return object
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function create_bridge_connection(
        int $workspaceId,
        int $teamId,
        string $bridgeType,
        int $bridgeId
    ): object {
        global $USER;

        $workspace = workspace::from_id($workspaceId);
        $team      = anderspink::from_id($teamId);

        /** @var briefing|board $class */
        $class    = self::RESOURCETYPE[$bridgeType];
        $resource = $class::get_from_params(['team' => $team->get_id(), 'api_id' => $bridgeId]);

        $record = anderspink_bridge::repository()
            ->where('team', $team->get_id())
            ->where('workspace', $workspace->get_id())
            ->where('type_id', $bridgeId);

        if ((!empty($record) || $record->exists()) && $record->type == $bridgeType) {
            throw new coding_exception(
                "We are unable to link {$workspace->get_name()} and {$resource->name} as it already exists"
            );
        }

        $anderspinkBridge            = new anderspink_bridge();
        $anderspinkBridge->team      = $team->get_id();
        $anderspinkBridge->workspace = $workspace->get_id();
        $anderspinkBridge->type      = $bridgeType;
        $anderspinkBridge->type_id   = $resource->apiid;
        $anderspinkBridge->user      = $USER->id;

        $bridge = $anderspinkBridge->save();

        $data = [
            'objectid'  => $anderspinkBridge->id,
            'contextid' => $workspace->get_context()->id,
            'other'     => [
                'user'         => $USER->id,
                'apiid'        => $resource->apiid,
                'endpoint'     => $resource::ENDPOINT,
                'teamid'       => $team->get_id(),
                'resourcename' => $resource->name,
                'workspaceid'  => $workspace->get_id(),
            ],
        ];

        $adhock = new sync_anderspink_content_adhoc_task();
        $adhock->set_custom_data($data);

        manager::queue_adhoc_task($adhock);

        return (object)[
            'workspaceid'    => $workspace->get_id(),
            'workspace_name' => $workspace->get_name(),
            'teamid'         => $team->get_id(),
            'team_name'      => $team->team_name,
            'type_name'      => $resource->name,
            'bridge_type'    => $bridgeType,
            'id'             => $bridge->id,
        ];
    }

    /**
     * @param  String  $apiKey
     *
     * @return bool
     * @throws coding_exception
     */
    private static function validateApiKey(string $apiKey): bool
    {
        global $DB;

        $record = anderspink_api::repository()->where('api_key', $apiKey);

        if ($record->exists()) {
            return false;
        }

        $anderspinkClient = new AnderspinkApiClient($apiKey);

        return $anderspinkClient->validateKey();
    }
}