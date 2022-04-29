<?php

namespace container_anderspink;

use coding_exception;
use container_anderspink\entity\anderspink_briefings as anderspink_briefings_entity;
use dml_exception;
use stdClass;

/**
 * Class briefing
 *
 * @package container_anderspink
 */
final class briefing
{
    public $id;
    public $team;
    public $name;
    public $type;
    public $apiid;
    public $img;

    private $entity;

    public const ENDPOINT = 'briefings';

    /**
     * @param  int  $apiid
     *
     * @return briefing|bool
     * @throws coding_exception
     */
    public static function from_apiid(int $apiid)
    {
        $record = anderspink_briefings_entity::repository()->where('api_id', $apiid);

        if (empty($record) || !$record->exists()) {
            return false;
        }

        $anderpinkBriefings = new briefing();
        $anderpinkBriefings->map_record((object) $record->one()->to_array());

        return $anderpinkBriefings;
    }

    /**
     * @param  int  $teamid
     *
     * @return array
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function from_teamid(int $teamid): array
    {
        $anderspinkBriefings = [];
        $records             = anderspink_briefings_entity::repository()->where('team', $teamid)->get();

        foreach ($records as $record) {
            $briefing = new briefing();
            $briefing->map_record((object) $record->to_array());

            $anderspinkBriefings[] = $briefing;
        }

        return $anderspinkBriefings;
    }

    /**
     * @param  array  $params
     *
     * @return briefing
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_from_params(array $params): briefing
    {
        global $DB;

        $record = $DB->get_record(anderspink_briefings_entity::TABLE, $params);

        $anderpinkBriefings = new briefing();
        $anderpinkBriefings->map_record($record);

        return $anderpinkBriefings;
    }

    /**
     * @param  int  $id
     *
     * @return briefing
     * @throws coding_exception
     */
    public static function from_id(int $id): briefing
    {
        $record = anderspink_briefings_entity::repository()->find_or_fail($id);

        $briefing = new briefing();
        $briefing->map_record((object) $record->to_array());

        return $briefing;
    }

    /**
     * @param  stdClass  $record
     *
     * @throws coding_exception
     */
    protected function map_record(stdClass $record): void
    {
        if (!isset($record->id)) {
            throw new coding_exception("No id found");
        }

        if (!isset($this->entiry)) {
            $this->entity = new anderspink_briefings_entity();
        }

        if (property_exists($record, 'id')) {
            $this->entity->id = $record->id;
            $this->id         = $record->id;
        }

        if (property_exists($record, 'team')) {
            $this->entity->team = $record->team;
            $this->team         = $record->team;
        }

        if (property_exists($record, 'name')) {
            $this->entity->name = $record->name;
            $this->name         = $record->name;
        }

        if (property_exists($record, 'type')) {
            $this->entity->type = $record->type;
            $this->type         = $record->type;
        }

        if (property_exists($record, 'api_id')) {
            $this->entity->api_id = $record->api_id;
            $this->apiid          = $record->api_id;
        }

        if (property_exists($record, 'img')) {
            $this->entity->img = $record->img;
            $this->img         = $record->img;
        }
    }
}