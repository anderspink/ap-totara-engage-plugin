<?php

require_once("{$CFG->dirroot}/container/type/anderspink/classes/local/AnderspinkApiClient.php");

use container_anderspink\entity\anderspink_boards;
use container_anderspink\entity\anderspink_briefings;
use container_anderspink\local\AnderspinkApiClient;

/**
 * @throws dml_exception
 */
function fetch_briefings()
{
    global $DB;

    $teams = $DB->get_records('anderspink_api_settings');
    $DB->execute("TRUNCATE TABLE {anderspink_briefings}");

    foreach ($teams as $team) {
        $anderspinkClient = new AnderspinkApiClient($team->api_key);
        $briefingsFromApi = $anderspinkClient->fetchBriefings();

        if (!$briefingsFromApi) {
            continue;
        }

        foreach ($briefingsFromApi as $type => $api) {
            foreach ($api as $a) {

                $briefing = (object) [
                    'team'   => $team->id,
                    'name'   => $a->name,
                    'type'   => $type,
                    'api_id' => $a->id,
                    'img'    => $a->image ?? '',
                ];

                $DB->insert_record('anderspink_briefings', $briefing);
            }
        }
    }
}

/**
 * @throws dml_exception
 */
function fetch_boards()
{
    global $DB;

    $teams = $DB->get_records('anderspink_api_settings');
    $DB->execute("TRUNCATE TABLE {anderspink_boards}");

    foreach ($teams as $team) {
        $anderspinkClient = new AnderspinkApiClient($team->api_key);
        $briefingsFromApi = $anderspinkClient->fetchBoards();

        if (!$briefingsFromApi) {
            continue;
        }

        foreach ($briefingsFromApi as $type => $api) {
            foreach ($api as $a) {
                $briefing = (object) [
                    'team'   => $team->id,
                    'name'   => $a->name,
                    'type'   => $type,
                    'api_id' => $a->id,
                    'img'    => $a->image ?? '',
                ];
                $DB->insert_record('anderspink_boards', $briefing);
            }
        }
    }
}