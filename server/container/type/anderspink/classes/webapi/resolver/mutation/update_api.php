<?php

namespace container_anderspink\webapi\resolver\mutation;

use coding_exception;
use container_anderspink\local\anderspink_helper;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use core\webapi\resolver\has_middleware;
use dml_exception;

class update_api implements mutation_resolver, has_middleware
{
    /**
     * @param array $args
     * @param execution_context $ec
     * @return object
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function resolve(array $args, execution_context $ec): object
    {
        if (empty($id = $args['id'])) {
            throw new coding_exception("Cannot edit unknown entry");
        }

        if (empty($teamName = $args['name'])){
            throw new coding_exception("Cannot create a new api entry with no team name");
        }

        if(empty($apiKey = $args['key'])) {
            throw new coding_exception("Cannot create a new api entry with no api key");
        }

        $result = anderspink_helper::edit_api($id, trim($teamName));

        if (!$result) {
            throw new coding_exception("Cannot edit an api.");
        }

        return $result;

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