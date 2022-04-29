<?php

namespace container_workspace;

use container_workspace\entity\workspace_discussion;
use core\orm\query\builder;
use core\plugininfo\container;
use totara_core\advanced_feature;

final class plugininfo extends container
{
    public function get_usage_for_registration_data()
    {
        $data                            = [];
        $data['numworkspaces']           = builder::table('workspace')->count();
        $data['numworkspacediscussions'] = workspace_discussion::repository()->count_all_non_deleted();
        $data['workspacesenabled']       = (int) advanced_feature::is_enabled('container_workspace');

        return $data;
    }
}