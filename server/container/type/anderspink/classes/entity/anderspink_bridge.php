<?php

namespace container_anderspink\entity;

use core\orm\entity\entity;

/**
 * Class anderspink_bridge
 *
 * @package container_anderspink\entity
 *
 * @property int $id
 * @property int $team
 * @property int $workspace
 * @property string $type
 * @property int $type_id
 * @property int $user
 *
 */
final class anderspink_bridge extends entity
{
    public const TABLE = 'anderspink_bridge';
}