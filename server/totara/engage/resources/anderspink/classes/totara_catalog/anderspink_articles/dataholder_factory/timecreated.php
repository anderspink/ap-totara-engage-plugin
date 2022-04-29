<?php

namespace engage_anderspink\totara_catalog\anderspink_articles\dataholder_factory;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use lang_string;
use totara_catalog\dataformatter\formatter;
use totara_catalog\dataformatter\sort_time;
use totara_catalog\dataformatter\user_date;
use totara_catalog\dataholder;
use totara_catalog\dataholder_factory;

class timecreated extends dataholder_factory
{

    /**
     * @return dataholder[]
     * @throws coding_exception
     */
    public static function get_dataholders(): array
    {
        return [
            new dataholder(
                'timecreated',
                new lang_string('field:timecreated', 'engage_anderspink'),
                [
                    formatter::TYPE_PLACEHOLDER_TEXT => new user_date(
                        'namesource.timecreated'
                    ),
                    formatter::TYPE_SORT_TIME        => new sort_time(
                        'namesource.timecreated'
                    ),
                ],
            ),
        ];
    }
}
