<?php

namespace engage_anderspink\totara_catalog\anderspink_articles\observer;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use totara_catalog\observer\object_update_observer;

/**
 * update catalog items based on removed tags
 */
class tag_removed extends object_update_observer
{

    public function get_observer_events(): array
    {
        return [
            '\core\event\tag_removed',
        ];
    }

    /**
     * init update for catalog items based on removed tag
     */
    protected function init_change_objects(): void
    {
        global $DB;

        $data      = new stdClass();
        $eventdata = $DB->get_records(
            'tag_instance',
            ['itemtype' => 'engage_anderspink', 'id' => $this->event->objectid],
            '',
            'id, itemid, contextid'
        );

        foreach ($eventdata as $updatetag) {
            $data->objectid  = $updatetag->itemid;
            $data->contextid = $updatetag->contextid;
            $this->register_for_update($data);
        }
    }
}
