<?php

namespace engage_anderspink\totara_catalog\anderspink_articles\dataholder_factory;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use dml_exception;
use totara_catalog\dataformatter\formatter;
use totara_catalog\dataformatter\fts;
use totara_catalog\dataformatter\ordered_list;
use totara_catalog\dataholder;
use totara_catalog\dataholder_factory;

class topics extends dataholder_factory
{

    /**
     * Override the parent dataholder completely so we can rename tags => topics.
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_dataholders(): array
    {
        global $CFG, $DB;

        if (empty($CFG->usetags)) {
            return [];
        }

        $collectionid = \core_tag_area::get_collection('engage_anderspink', 'engage_resource');
        $coll         = $DB->get_record('tag_coll', ['id' => $collectionid], '*', MUST_EXIST);
        $displayname  = \core_tag_collection::display_name($coll);

        if (!\core_tag_area::is_enabled('engage_anderspink', 'engage_resource')) {
            return [];
        }

        return [
            new dataholder(
                'ftstags',
                $displayname,
                [formatter::TYPE_FTS => new fts('ftstags.tags')],
                [
                    'ftstags' =>
                        "LEFT JOIN (SELECT er.instanceid, {$DB->sql_group_concat('t.name',',')} AS tags
                                      FROM {tag_instance} ti
                                      JOIN {tag} t ON t.id = ti.tagid
                                      JOIN {engage_resource} er ON er.id = ti.itemid
                                     WHERE ti.component = 'engage_anderspink'
                                     GROUP BY er.instanceid) ftstags
                           ON ftstags.instanceid = base.id",
                ]
            ),
            new dataholder(
                'tags',
                $displayname,
                [
                    formatter::TYPE_PLACEHOLDER_TEXT => new ordered_list('tags.tags'),
                ],
                [
                    'tags' =>
                        "LEFT JOIN (SELECT er.instanceid, {$DB->sql_group_concat('t.rawname',',')} AS tags
                                      FROM {tag_instance} ti
                                      JOIN {tag} t ON t.id = ti.tagid
                                      JOIN {engage_resource} er ON er.id = ti.itemid
                                     WHERE ti.component = 'engage_anderspink'
                                     GROUP BY er.instanceid) tags
                           ON tags.instanceid = base.id",
                ]
            ),
        ];

    }
}
