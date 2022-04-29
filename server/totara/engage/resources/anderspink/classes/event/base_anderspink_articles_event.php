<?php

namespace engage_anderspink\event;

use coding_exception;
use context_user;
use core\event\base;
use core_ml\event\interaction_event;
use core_ml\event\public_access_aware_event;
use engage_anderspink\entity\anderspink_articles;

abstract class base_anderspink_articles_event extends base implements interaction_event, public_access_aware_event
{
    /**
     * @return void
     */
    protected function init(): void
    {
        $this->data['objecttable'] = anderspink_articles::TABLE;
        $this->data['edulevel']    = self::LEVEL_OTHER;
    }

    /**
     * @param          $resource
     * @param int|null $actorid
     *
     * @return base
     * @throws coding_exception
     */
    public static function from_articles($resource, int $actorid = null): base
    {
        global $USER;

        if (!$resource->is_exists(true)) {
            throw new coding_exception("Unable to create an event for the not-existing article");
        }

        if (null == $actorid) {
            $actorid = $USER->id;
        }

        $ownerid = $resource->get_userid();
        $context = context_user::instance($ownerid);

        $data = [
            'objectid'      => $resource->get_instanceid(),
            'context'       => $context,
            'userid'        => $actorid,
            'relateduserid' => $ownerid,
            'other'         => [
                'name'       => $resource->get_name(false),
                'resourceid' => $resource->get_id(),
                'owner_id'   => $ownerid,
                'is_public'  => $resource->is_public(),
            ],
        ];

        /** @var base_anderspink_articles_event $event */
        return static::create($data);
    }

    /**
     * @return string
     */
    public function get_component(): string
    {
        return anderspink_articles::get_resource_type();
    }

    /**
     * @return int
     */
    public function get_rating(): int
    {
        return 1;
    }

    /**
     * @return int
     */
    public function get_user_id(): int
    {
        return $this->userid;
    }

    /**
     * @return int
     */
    public function get_item_id(): int
    {
        return $this->other['resourceid'];
    }

    /**
     * @return string|null
     */
    public function get_area(): ?string
    {
        return null;
    }

    /**
     * @return bool
     */
    public function is_public(): bool
    {
        return $this->other['is_public'];
    }
}