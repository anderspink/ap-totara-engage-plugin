<?php

namespace engage_anderspink\totara_engage\modal;

use coding_exception;
use container_anderspink\entity\anderspink_api;
use dml_exception;
use totara_engage\modal\modal;
use totara_tui\output\component;

/**
 * A modal medata for the front-end component.
 */
final class anderspink_modal extends modal
{
    /**
     * @return component
     */
    public function get_vue_component(): component
    {
        return new component('anderspink/pages/resources/Articles');
    }

    /**
     * @return string
     * @throws coding_exception
     */
    public function get_label(): string
    {
        return get_string('anderspink_resources', 'engage_anderspink');
    }

    /**
     * @return bool
     */
    public function is_expandable(): bool
    {
        return true;
    }

    /**
     * @return int
     */
    public function get_order(): int
    {
        return 500; //always be last
    }

    /**
     * @return bool
     * @throws dml_exception
     */
    public function show_modal(): bool
    {
        global $USER, $DB;

        if ($DB->count_records(anderspink_api::TABLE) === 0) {
            return false;
        }

        return has_capability_in_any_context('engage/anderspink:manage', null, $USER->id);
    }
}