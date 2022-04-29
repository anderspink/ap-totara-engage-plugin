<?php

namespace container_anderspink\entity;

use core\orm\entity\entity;

/**
 * Class anderspink_api
 *
 * @property int $id
 * @property string $api_key
 * @property string $team_name
 * @property int $timecreated
 * @property int $timemodified
 */
final class anderspink_api extends entity
{
    public const TABLE = 'anderspink_api_settings';
}