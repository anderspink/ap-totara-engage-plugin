<?php
namespace engage_anderspink\event;

use coding_exception;
use engage_anderspink\totara_engage\resource\anderspink_articles;

final class anderspink_articles_created extends base_anderspink_articles_event
{
    /**
     * @param $resource
     * @param int|null $actor_id
     *
     * @return base_anderspink_articles_event
     * @throws coding_exception
     */
    public static function from_article($resource, int $actor_id = null): base_anderspink_articles_event
    {
        if (empty($actor_id)) {
            // Normally the user who created articles should be the same user that created this event.
            // This should be rarely happening, unless the upstream code is using this event wrongly.
            $actor_id = $resource->get_userid();
        }

        return parent::from_articles($resource, $actor_id);
    }

    /**
     * @return void
     */
    protected function init(): void
    {
        parent::init();
        $this->data['crud']     = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * @return string
     * @throws coding_exception
     */
    public static function get_name()
    {
        return get_string('articlecreated', 'engage_anderspink');
    }

    /**
     * @return string
     */
    public function get_interaction_type(): string
    {
        return 'create';
    }
}