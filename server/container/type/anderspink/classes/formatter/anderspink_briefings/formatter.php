<?php

namespace container_anderspink\formatter\briefings;

use container_anderspink\briefing;
use context_user;
use core\webapi\formatter\formatter as base_formatter;

final class formatter extends base_formatter
{
    public function __construct(briefing $briefing)
    {
        global $USER;

        $context = context_user::instance($USER->id);

        $record = (object) [
            'id'     => $briefing->id,
            'team'   => $briefing->name,
            'type'   => $briefing->type,
            'api_id' => $briefing->apiid,
            'img'    => $briefing->img,
        ];

        parent::__construct($record, $context);
    }

    /**
     * @return array
     */
    protected function get_map(): array
    {
        return [
            'id'     => null,
            'team'   => null,
            'type'   => null,
            'api_id' => null,
            'img'    => null,
        ];
    }
}