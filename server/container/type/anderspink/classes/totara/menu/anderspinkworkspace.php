<?php

namespace container_anderspink\totara\menu;

use coding_exception;
use totara_core\advanced_feature;
use totara_core\totara\menu\item;

/**
 * Class your_spaces
 *
 * @package container_workspace\totara\menu
 */
final class anderspinkworkspace extends item {
    /**
     * @return int|null
     */
    public function get_default_sortorder(): ?int {
        return 50000;
    }

    /**
     * @return string
     */
    protected function get_default_url(): string {
        return '/container/type/anderspink/api_settings.php';
    }

    /**
     * @return string
     * @throws coding_exception
     */
    protected function get_default_title(): string {
        return get_string('workspace', 'container_anderspink');
    }

    /**
     * @return string
     */
    protected function get_default_parent(): string {
        return '\container_workspace\totara\menu\collaborate';
    }

    /**
     * @return bool|void
     * @throws coding_exception
     */
    protected function check_visibility(): bool {
        global $USER;
        if (!isloggedin() or isguestuser()) {
            return false;
        }

        if (!advanced_feature::is_enabled('container_workspace')) {
            return false;
        }

        // Must have the view capability
        $context = \context_user::instance($USER->id);

        return has_capability('container/anderspink:manage', $context, $USER->id);
    }
}