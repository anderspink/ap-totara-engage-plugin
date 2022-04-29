<?php

namespace engage_anderspink\totara_catalog\anderspink_articles\dataholder_factory;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use engage_anderspink\totara_catalog\anderspink_articles\dataformatter\image as image_formatter;
use totara_catalog\dataformatter\formatter;
use totara_catalog\dataholder;
use totara_catalog\dataholder_factory;

class image extends dataholder_factory
{

    /**
     * @return dataholder[]
     * @throws coding_exception
     */
    public static function get_dataholders(): array
    {
        return [
            new dataholder(
                'image',
                'not used image dataholder',
                [
                    formatter::TYPE_PLACEHOLDER_IMAGE => new image_formatter(
                        'imagesource.id',
                        'imagesource.name',
                        'imagesource.userid'
                    ),
                ],
                [
                    'imagesource' =>
                        "LEFT JOIN {engage_resource} imagesource
                                ON imagesource.instanceid = base.id
                               AND imagesource.resourcetype = 'engage_anderspink'",
                ]
            ),
        ];
    }
}
