<?php

namespace container_anderspink\webapi\resolver\type;

use coding_exception;
use container_anderspink\briefing as model;
use core\webapi\execution_context;
use core\webapi\type_resolver;

final class briefing implements type_resolver
{
    public static function resolve(string $field, $source, array $args, execution_context $ec)
    {
        if (!($source instanceof model)) {
            throw new coding_exception("Invalid paramter that is not type of " . model::class);
        }

        switch ($field) {
            default:
                return $source->{$field};
        }
    }
}