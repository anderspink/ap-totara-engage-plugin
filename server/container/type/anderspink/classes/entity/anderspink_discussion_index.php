<?php

namespace container_anderspink\entity;

use core\orm\entity\entity;

/**
 * Class anderspink_discussion_index
 *
 * @property int $id
 * @property int $discussion
 * @property int $timecreated
 *
 * @package container_anderspink\entity
 */
final class anderspink_discussion_index extends entity
{
    public const TABLE = 'anderspink_discussion_index';
}