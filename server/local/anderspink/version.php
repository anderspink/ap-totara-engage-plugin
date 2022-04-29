<?php
defined('MOODLE_INTERNAL') || die();


$plugin->version   = 2022031701;                 // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2017111309;                 // Requires this Moodle version.
$plugin->component = 'local_anderspink';         // To check on upgrade, that module sits in correct place

$plugin->dependencies = [
    'container_workspace'  => 2020100108,
    'container_anderspink' => 2022012601,
    'engage_anderspink'    => 2021112501,
];
