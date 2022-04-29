<?php

namespace engage_anderspink\totara_catalog\anderspink_articles\feature_factory;

defined('MOODLE_INTERNAL') || die();

use totara_catalog\datasearch\all;
use totara_catalog\feature;
use totara_catalog\feature_factory;
use totara_catalog\local\config;

class topics extends feature_factory
{

    /**
     * This is mostly taken from core_tag\totara_catalog\feature_factory::get_features
     * with a few tweaks to display the topics collection correctly
     */
    public static function get_features(): array
    {
        global $CFG, $DB;

        $component  = 'engage_anderspink';
        $itemtype   = 'engage_resource';
        $objecttype = 'engage_anderspink';

        if (empty($CFG->usetags)) {
            return [];
        }

        if (!\core_tag_area::is_enabled($component, $itemtype)) {
            return [];
        }

        $collectionid = \core_tag_area::get_collection($component, $itemtype);
        $coll         = $DB->get_record('tag_coll', ['id' => $collectionid], '*', MUST_EXIST);
        $displayname  = \core_tag_collection::display_name($coll);

        $datafilter = new all(
            'tag_featured_' . $collectionid,
            'catalog',
            ['objecttype', 'objectid'],
            'LEFT JOIN'
        );

        $tagidparamkey    = 'tfe_' . $collectionid . '_tagid_' . $objecttype;
        $itemtypeparamkey = 'tfe_' . $collectionid . '_type_' . $objecttype;
        $alias            = 'tfe_' . $objecttype;

        $datafilter->add_source(
            'notused',
            "(SELECT engage_resource.instanceid, 1 AS featured
                      FROM {tag_instance} tag_instance
                      JOIN {engage_resource} engage_resource ON engage_resource.id = tag_instance.itemid
                     WHERE tag_instance.tagid = :{$tagidparamkey}
                       AND tag_instance.itemtype = :{$itemtypeparamkey})",
            $alias,
            [
                'objecttype' => "'{$objecttype}'",
                'objectid'   => "{$alias}.instanceid",
            ],
            "",
            [
                $tagidparamkey    => config::instance()->get_value('featured_learning_value'),
                $itemtypeparamkey => $itemtype,
            ],
            [
                'featured' => 1,
            ]
        );

        $feature = new feature(
            'tag_' . $collectionid,
            $displayname,
            $datafilter
        );

        $feature->add_options_loader(self::get_options_loader($itemtype));

        return [$feature];
    }

    /**
     * @param string $itemtype
     * @return callable
     */
    private static function get_options_loader(string $itemtype): callable
    {
        return function () use ($itemtype) {
            global $DB;

            $sql    = "
                SELECT DISTINCT tag.id, tag.name
                  FROM {tag_instance} tag_instance
                  JOIN {tag} tag
                    ON tag_instance.tagid = tag.id
                 WHERE tag_instance.itemtype = :itemtype
            ";
            $params = ['itemtype' => $itemtype];

            $records = $DB->get_records_sql($sql, $params);

            $systemcontext = \context_system::instance();

            $options = [];
            foreach ($records as $record) {
                $options[$record->id] = format_string($record->name, true, ['context' => $systemcontext]);
            }

            return $options;
        };
    }
}
