<?php

namespace container_anderspink\formatter\anderspink_api;

use container_anderspink\anderspink;
use context_user;
use core\webapi\formatter\formatter as base_formatter;

/**
 * Class workspace_formatter
 *
 * @package container_workspace\formatter
 */
final class formatter extends base_formatter
{
    /**
     * workspace_formatter constructor.
     *
     * @param anderspink $anderspink
     */
    public function __construct(anderspink $anderspink)
    {
        global $USER;

        $context = context_user::instance($USER->id);

        $record = (object) [
            'id'           => $anderspink->get_id(),
            'api_key'      => $anderspink->api_key,
            'team_name'    => $anderspink->team_name,
            'timecreated'  => $anderspink->timecreated,
            'timemodified' => $anderspink->timemodified,
        ];

        parent::__construct($record, $context);
    }

    /**
     * @return array
     */
    protected function get_map(): array
    {
        return [
            'id'           => null,
            'api_key'      => null,
            'team_name'    => null,
            'timecreated'  => null,
            'timemodified' => null,
        ];
    }
}
