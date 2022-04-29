<?php

namespace engage_anderspink\totara_catalog\anderspink_articles\observer;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core_user\totara_engage\share\recipient\user;
use dml_exception;
use stdClass;
use totara_catalog\observer\object_update_observer;

/**
 * update catalog data based on update or create article id
 */
class anderspink_articles extends object_update_observer
{

    public function get_observer_events(): array
    {
        return [
            '\engage_anderspink\event\anderspink_articles_created',
            '\engage_anderspink\event\anderspink_articles_updated',
        ];
    }

    /**
     * Adds or updates an items visibility cache
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    private function refresh_item_cache(): void
    {
        global $DB;

        $sql = "SELECT er.instanceid as id, er.access, er.userid, {$DB->sql_group_concat('esr.instanceid',',')} AS accessors
                  FROM {engage_resource} er
             LEFT JOIN {engage_share} es
                    ON er.id = es.itemid
                   AND er.resourcetype = es.component
             LEFT JOIN {engage_share_recipient} esr
                    ON es.id = esr.shareid
                   AND esr.area = :area
                   and esr.component = :component
                 WHERE er.resourcetype = :type
                   AND er.instanceid = :instanceid
              GROUP BY er.instanceid, er.access, er.userid";

        $params = [
            'instanceid' => $this->event->objectid,
            'area'       => user::AREA,
            'component'  => 'core_user',
            'type'       => \engage_anderspink\totara_engage\resource\anderspink_articles::get_resource_type(),
        ];

        $accessitem = $DB->get_record_sql($sql, $params, IGNORE_MULTIPLE);
        if ($accessitem) {
            $cache = \cache::make('engage_anderspink', 'catalog_visibility');
            $cache->set($accessitem->id, $accessitem);
        }
    }

    /**
     * init article update object for created or updated article
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function init_change_objects(): void
    {
        $this->refresh_item_cache();

        $data            = new stdClass();
        $data->objectid  = $this->event->objectid;
        $data->contextid = $this->event->contextid;

        $this->register_for_update($data);
    }
}
