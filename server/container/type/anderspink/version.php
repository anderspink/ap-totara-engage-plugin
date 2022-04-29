<?php
defined('MOODLE_INTERNAL') || die();


$plugin->version   = 2022033101;                 // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2017111309;                 // Requires this Moodle version.
$plugin->component = 'container_anderspink';     // To check on upgrade, that module sits in correct place

$plugin->dependencies = [
    'container_workspace' => 2020100108,
    'local_anderspink'    => 2021012601,
];
