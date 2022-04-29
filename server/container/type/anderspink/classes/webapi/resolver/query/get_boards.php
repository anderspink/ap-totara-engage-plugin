<?php

namespace container_anderspink\webapi\resolver\query;

use coding_exception;
use container_anderspink\board;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use core\webapi\resolver\has_middleware;

final class get_boards implements query_resolver, has_middleware
{

    /**
     * @param array $args
     * @param execution_context $ec
     * @return array|mixed
     * @throws \dml_exception
     * @throws coding_exception
     */
    public static function resolve(array $args, execution_context $ec)
    {
        if (empty($teamid = $args['teamid'])) {
            throw new coding_exception("Cannot fetch entry with no team name");
        }

        return board::from_teamid($teamid);
    }

    /**
     * @return array
     */
    public static function get_middleware(): array
    {
        return [
            new require_login(),
            new require_advanced_feature('container_workspace'),
        ];
    }
}