<?php

namespace container_anderspink;

use coding_exception;
use container_anderspink\entity\anderspink_boards as anderspink_boards_entity;
use dml_exception;
use stdClass;

/**
 * Class briefing
 *
 * @package container_anderspink
 */
final class board
{
    public $id;
    public $team;
    public $name;
    public $type;
    public $apiid;
    public $img;

    private $entity;

    public const ENDPOINT = 'boards';

    /**
     * @param  int  $apiid
     *
     * @return board|bool
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function from_apiid(int $apiid)
    {
        $record = anderspink_boards_entity::repository()->where('api_id', $apiid);

        if (empty($record) || !$record->exists()) {
            return false;
        }

        $anderpinkBoards = new board();
        $anderpinkBoards->map_record((object)$record->one()->to_array());

        return $anderpinkBoards;
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
        $anderpinkBoards = [];
        $records         = anderspink_boards_entity::repository()->where('team', $teamid)->get();

        foreach ($records as $record) {
            $boards = new board();
            $boards->map_record((object)$record->to_array());

            $anderpinkBoards[] = $boards;
        }

        return $anderpinkBoards;
    }

    /**
     * @param  array  $params
     *
     * @return board
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_from_params(array $params): board
    {
        global $DB;

        $record = $DB->get_record(anderspink_boards_entity::TABLE, $params);

        $anderpinkBoards = new board();
        $anderpinkBoards->map_record($record);

        return $anderpinkBoards;
    }

    /**
     * @param  int  $id
     *
     * @return board
     * @throws coding_exception
     */
    public static function from_id(int $id): board
    {
        $record = anderspink_boards_entity::repository()->find_or_fail($id);
        $board  = new board();

        $board->map_record((object)$record->to_array());

        return $board;
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
            $this->entity = new anderspink_boards_entity();
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