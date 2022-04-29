<?php
namespace engage_anderspink\event;

final class anderspink_articles_reshared extends base_anderspink_articles_event {
    /**
     * @return void
     */
    protected function init(): void {
        parent::init();
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * @return string
     */
    public static function get_name() {
        return get_string('articlereshared', 'engage_anderspink');
    }

    /**
     * @return string
     */
    public function get_interaction_type(): string {
        return 'reshare';
    }
}