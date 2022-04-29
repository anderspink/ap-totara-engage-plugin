<?php

use core\orm\query\builder;

defined('MOODLE_INTERNAL') || die();

global $PAGE;

$settings = new admin_settingpage('local_anderspink', get_string('pluginname', 'local_anderspink'));
$ADMIN->add('localplugins', $settings);

$settings->add(
    new admin_setting_heading(
        'anderspink_discussion_post_user', '', get_string('pluginadministration', 'local_anderspink')
    )
);

$default    = builder::table('config')->where('name', 'anderspink_discussion_post_user')->one();
$role       = builder::table('role')->where('shortname', 'workspacediscussionauthor')->one();
$listOfUser = get_role_users($role->id, \context_system::instance());
$parsed     = [];

if (sizeof($listOfUser) <= 1) {
    set_config('anderspink_discussion_post_user', 0);
    return;
}

foreach ($listOfUser as $user) {
    $parsed[$user->id] = "{$user->firstname} {$user->lastname} ({$user->email})";
}

asort($parsed);

if (!array_key_exists($default->value, $parsed)) {
    set_config('anderspink_discussion_post_user', array_key_first($parsed));
}

$settings->add(
    new admin_setting_configselect(
        'anderspink_discussion_post_user',
        get_string('plugin:settings:visible:discussion_user', 'local_anderspink'),
        get_string('plugin:setting:description:discussion_user', 'local_anderspink'),
        null,
        $parsed
    )
);

$PAGE->requires->js_call_amd(
    'core/form-autocomplete', 'enhance', ['#id_s__anderspink_discussion_post_user', false, '', 'User', false, true, '']
);

//Custom styles
echo "
<style>
    .form-autocomplete-downarrow {
        left: 19.5em !important;
        top: -2.2em !important;
    }
    .form-warning {
        display: none !important;
    }
</style>
";