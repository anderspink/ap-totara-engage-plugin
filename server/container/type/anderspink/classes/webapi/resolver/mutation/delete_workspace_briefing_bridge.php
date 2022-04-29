<?php

namespace container_anderspink\webapi\resolver\mutation;

use coding_exception;
use container_anderspink\bridge;
use container_anderspink\local\anderspink_helper;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use core\webapi\resolver\has_middleware;
use dml_exception;

final class delete_workspace_briefing_bridge implements mutation_resolver, has_middleware
{

    /**
     * @param array $args
     * @param execution_context $ec
     * @return bool
     * @throws coding_exception
     */
    public static function resolve(array $args, execution_context $ec): bool
    {
        $id = $args['id'];

        if (empty($id) || $id < 1){
            throw new coding_exception("Cannot find a record with provided ID");
        }

        $result = bridge::delete($id);

        if (!$result) {
            throw new coding_exception("Could not delete an api key with id {$id}");
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array
    {
        return [
            new require_login(),
            new require_advanced_feature('container_workspace'),
        ];
    }
}