<?php

namespace engage_anderspink\event;

use coding_exception;
use core\event\base;
use totara_engage\entity\share_recipient;
use totara_engage\share\share as share_model;

final class anderspink_articles_shared extends base {
    /**
     * @return void
     */
    protected function init(): void {
        $this->data['objecttable'] = share_recipient::TABLE;
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Create an event for a share recipient.
     *
     * @param share_model $share
     * @param int|null $actorid
     * @return base_anderspink_articles_event|base
     * @throws coding_exception
     */
    public static function from_share(share_model $share, int $actorid = null)
    {
        if (null == $actorid) {
            $actorid = $share->get_sharer_id();
        }

        $context = \context_user::instance($share->get_sharer_id());

        $data = [
            'objectid' => $share->get_recipient_id(),
            'context' => $context,
            'userid' => $actorid,
        ];

        /** @var base_anderspink_articles_event $event */
        return anderspink_articles_shared::create($data);
    }

    /**
     * @return string
     * @throws coding_exception
     */
    public static function get_name() {
        return get_string('articleshared', 'engage_anderspink');
    }
}