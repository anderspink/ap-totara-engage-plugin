<?php

namespace engage_anderspink\totara_catalog\anderspink_articles\observer;

defined('MOODLE_INTERNAL') || die();

use totara_catalog\observer\object_update_observer;

/**
 * Update catalog data based on deleted article.
 */
class anderspink_articles_deleted extends object_update_observer
{

    public function get_observer_events(): array
    {
        return [
            '\engage_anderspink\event\article_deleted',
        ];
    }

    /**
     * Init article remove object for deleted article.
     */
    protected function init_change_objects(): void
    {
        $this->register_for_delete($this->event->objectid);
    }
}
