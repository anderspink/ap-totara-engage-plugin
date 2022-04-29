<?php

use engage_anderspink\totara_engage\resource\anderspink_articles;
use totara_engage\access\access_manager;

defined('MOODLE_INTERNAL') || die();

/**
 * This is a callback from the file system. Use for serving the file to the user.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context  $context
 * @param string   $filearea
 * @param array    $args
 * @param bool     $forcedownload
 * @param array    $options
 *
 * @return void
 * @throws coding_exception
 */
function engage_anderspink_pluginfile(
    stdClass $course,
    stdClass $cm,
    context  $context,
    string   $filearea,
    array    $args,
    bool     $forcedownload,
    array    $options
): void {
    global $CFG, $USER;
    require_once("{$CFG->dirroot}/lib/filelib.php");

    if (!in_array($filearea, ['content', 'image'])) {
        return;
    }

    if (empty($CFG->publishgridcatalogimage) || !in_array($filearea,
            ['image']
        ) || empty($options['preview']) || $options['preview'] !== 'totara_catalog_medium') {
        //check just login as engage does not support guests
        if (!isloggedin()) {
            send_file_not_found();
        }

        /** @var anderspink_articles $article */
        $article = anderspink_articles::from_resource_id((int)$args[0]);
        if (!access_manager::can_access($article, $USER->id)) {
            send_file_not_found();
        }
    }

    $relativepath = implode("/", $args);
    $fullpath     = "/{$context->id}/engage_anderspink/{$filearea}/{$relativepath}";

    $fs = get_file_storage();
    $file = $fs->get_file_by_hash(sha1($fullpath));

    if (!$file) {
        return;
    }

    send_stored_file($file, 360, 0, $forcedownload, $options);
}
