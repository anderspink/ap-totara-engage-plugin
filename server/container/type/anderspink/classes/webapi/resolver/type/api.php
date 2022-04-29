<?php

namespace container_anderspink\webapi\resolver\type;

use coding_exception;
use container_anderspink\anderspink as model;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use DateTime;
use DateTimeZone;
use Exception;
use totara_core\advanced_feature;
use totara_core\feature_not_available_exception;

final class api implements type_resolver
{
    /**
     * @param string $field
     * @param mixed $source
     * @param array $args
     * @param execution_context $ec
     * @return array|mixed
     * @throws feature_not_available_exception
     * @throws coding_exception
     * @throws Exception
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec)
    {
        advanced_feature::require('container_workspace');

        if (!($source instanceof model)) {
            throw new coding_exception("Invalid parameter that is not type of " . model::class);
        }

        switch ($field) {
            case "timecreated":
            case "timemodified":
                $timezone = totara_get_clean_timezone(get_user_timezone());
                $dateTime = new DateTime("@" . (string) $source->{$field}, new DateTimeZone($timezone));

                return $dateTime->format('Y-m-d H:i:s');
            default:
            {
                return $source->{$field};
            }
        }
    }
}