<?php

namespace engage_anderspink\totara_catalog\anderspink_articles\dataholder_factory;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use lang_string;
use totara_catalog\dataformatter\formatter;
use totara_catalog\dataformatter\fts;
use totara_catalog\dataformatter\strip_tags;
use totara_catalog\dataformatter\text;
use totara_catalog\dataholder;
use totara_catalog\dataholder_factory;

class name extends dataholder_factory
{

    /**
     * @return dataholder[]
     * @throws coding_exception
     */
    public static function get_dataholders(): array
    {
        return [
            new dataholder(
                'name',
                new lang_string('field:name', 'engage_anderspink'),
                [
                    formatter::TYPE_FTS               => new fts(
                        'namesource.name'
                    ),
                    formatter::TYPE_PLACEHOLDER_TITLE => new text(
                        'namesource.name'
                    ),
                    formatter::TYPE_PLACEHOLDER_TEXT  => new text(
                        'namesource.name'
                    ),
                    formatter::TYPE_SORT_TEXT         => new strip_tags(
                        'namesource.name'
                    ),
                ],
                [
                    'namesource' =>
                        "LEFT JOIN {engage_resource} namesource
                                ON namesource.instanceid = base.id
                               AND namesource.resourcetype = 'engage_anderspink'",
                ]
            ),
        ];
    }
}
