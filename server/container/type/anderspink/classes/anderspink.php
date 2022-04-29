<?php

namespace container_anderspink;

use coding_exception;
use container_anderspink\entity\anderspink_api as anderspink_api_entity;
use core_container\container;
use core_container\facade\category_name_provider;
use dml_exception;
use stdClass;

/**
 * Class workspace
 *
 * @property int $id
 * @property string $api_key
 * @property string $team_name
 * @property int $timecreated
 * @property int $timemodified
 * @property $entity
 * @package container_workspace
 */
final class anderspink extends container implements category_name_provider
{
    public $id;
    public $api_key;
    public $team_name;
    public $timecreated;
    public $timemodified;

    /**
     * @var anderspink_api_entity|null
     */
    private $entity;

    /**
     * @param  int  $id
     *
     * @return anderspink
     * @throws coding_exception
     */
    public static function from_id(int $id): container
    {
        $record        = anderspink_api_entity::repository()->find_or_fail($id);
        $anderspinkApi = new anderspink();

        $anderspinkApi->map_record((object)$record->to_array());

        return $anderspinkApi;
    }

    /**
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function all(): array
    {
        $anderspinkRecords = [];
        $records           = anderspink_api_entity::repository()->get();

        foreach ($records as $record) {
            $anderspink = new anderspink();
            $anderspink->map_record((object)$record->to_array());

            $anderspinkRecords[] = $anderspink;
        }

        return $anderspinkRecords;
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

        if (!isset($this->entity)) {
            $this->entity = new anderspink_api_entity();
        }

        if (property_exists($record, 'id')) {
            $this->entity->id = $record->id;
            $this->id         = $record->id;
        }

        if (property_exists($record, 'api_key')) {
            $this->entity->api_key = $record->api_key;
            $this->api_key         = $record->api_key;
        }

        if (property_exists($record, 'team_name')) {
            $this->entity->team_name = $record->team_name;
            $this->team_name         = $record->team_name;
        }

        if (property_exists($record, 'timecreated')) {
            $this->entity->timecreated = $record->timecreated;
            $this->timecreated         = $record->timecreated;
        }

        if (property_exists($record, 'timemodified')) {
            $this->entity->timemodified = $record->timemodified;
            $this->timemodified         = $record->timemodified;
        }
    }

    /**
     * @return string
     * @throws coding_exception
     */
    public static function get_container_category_name(): string
    {
        return get_string('category_name', 'container_anderspink');
    }

    /**
     * @return \moodle_url
     */
    public function get_view_url(): \moodle_url
    {
        return new \moodle_url("/container/type/workspace/workspace.php");
    }
}