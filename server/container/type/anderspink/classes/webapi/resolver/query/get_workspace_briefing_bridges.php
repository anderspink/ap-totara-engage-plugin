<?php

namespace container_anderspink\webapi\resolver\query;

use coding_exception;
use container_anderspink\bridge;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use core\webapi\resolver\has_middleware;
use dml_exception;

final class get_workspace_briefing_bridges implements query_resolver, has_middleware
{
    /**
     * @param  array  $args
     * @param  execution_context  $ec
     *
     * @return array
     * @throws coding_exception
     */
    public static function resolve(array $args, execution_context $ec): array
    {
        return bridge::all();
    }

    public static function get_middleware(): array
    {
        return [
            new require_login(),
            new require_advanced_feature('container_workspace'),
        ];
    }
}