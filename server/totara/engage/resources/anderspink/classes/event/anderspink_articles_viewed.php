<?php
namespace engage_anderspink\event;

/**
 * Event for viewing article.
 */
final class anderspink_articles_viewed extends base_anderspink_articles_event {
    /**
     * @return void
     */
    protected function init(): void {
        parent::init();
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * @return string
     */
    public function get_interaction_type(): string {
        return 'view';
    }
}