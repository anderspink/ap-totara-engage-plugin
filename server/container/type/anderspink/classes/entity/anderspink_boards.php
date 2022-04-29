<?php

namespace container_anderspink\entity;

use core\orm\entity\entity;

/**
 * Class anderspink_boards
 *
 * @property int $id
 * @property int $team
 * @property string $name
 * @property string $type
 * @property int $api_id
 * @property string $img
 *
 * @package container_anderspink\entity
 */
final class anderspink_boards extends entity
{
    public const TABLE = 'anderspink_boards';
}