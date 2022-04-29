<?php
namespace engage_anderspink\event;

final class anderspink_articles_deleted extends base_anderspink_articles_event {
    /**
     * @return void
     */
    protected function init(): void {
        parent::init();
        $this->data['crud'] = 'd';
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public static function get_name() {
        return get_string('articledeleted', 'engage_anderspink');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' deleted the article with id '$this->objectid'" .
            "and the name of article is '{$this->other['name']}'.";
    }

    /**
     * @return string
     */
    public function get_interaction_type(): string {
        return 'delete';
    }
}