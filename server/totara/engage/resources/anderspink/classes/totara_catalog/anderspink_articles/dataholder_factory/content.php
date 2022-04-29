<?php

namespace engage_anderspink\totara_catalog\anderspink_articles\dataholder_factory;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use lang_string;
use totara_catalog\dataformatter\formatter;
use totara_catalog\dataformatter\fts;
use totara_catalog\dataholder;
use totara_catalog\dataholder_factory;

class content extends dataholder_factory
{

    /**
     * @return dataholder[]
     * @throws coding_exception
     */
    public static function get_dataholders(): array
    {
        return [
            new dataholder(
                'ftscontent',
                new lang_string('field:content', 'engage_anderspink'),
                [
                    formatter::TYPE_FTS => new fts(
                        'base.content'
                    ),
                ]
            ),
        ];
    }
}
