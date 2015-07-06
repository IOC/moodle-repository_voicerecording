<?php

//  Voice recording repository plugin for Moodle
//  Copyright © 2012  Institut Obert de Catalunya
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.


/**
 * A repository plugin to allow user create and upload audio files
 *
 * @since      2.3
 * @package    repository
 * @subpackage voice recording
 * @copyright  2012 Institut Obert de Catalunya
 * @author     Marc Català <mcatala@ioc.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib/filelib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

unset($SESSION->reponanogong);

$err = new stdClass();

/// Parameters
$action    = optional_param('action', '', PARAM_ALPHA);
$repo_id   = optional_param('repo_id', 0, PARAM_INT);           // Repository ID
$contextid = optional_param('ctx_id', SYSCONTEXTID, PARAM_INT); // Context ID
$maxbytes  = optional_param('maxbytes', 0, PARAM_INT);          // Maxbytes
$saveas_filename = optional_param('title', '', PARAM_ALPHANUMEXT); // Nanogong only accepts alphanumeric characters
$itemid = optional_param('itemid', 0, PARAM_INT);

list($context, $course, $cm) = get_context_info_array($contextid);
require_login($course, false, $cm);
$PAGE->set_context($context);

echo $OUTPUT->header(); // send headers
@header('Content-type: text/html; charset=utf-8');

if (!confirm_sesskey()) {
    $err->error = get_string('invalidsesskey', 'error');
    die(json_encode($err));
}

// Check permissions
$repo = repository::get_repository_by_id($repo_id, $contextid);
$repo->check_capability();

$moodle_maxbytes = get_max_upload_file_size();
// to prevent maxbytes greater than moodle maxbytes setting
if ($maxbytes == 0 || $maxbytes>=$moodle_maxbytes) {
    $maxbytes = $moodle_maxbytes;
}
/// Wait as long as it takes for this script to finish
set_time_limit(0);

if (file_exists($CFG->dirroot . '/repository/' . $repo->get_typename() . '/lib.php')) {
    require_once($CFG->dirroot . '/repository/'. $repo->get_typename() . '/lib.php');
    $classname = 'repository_' . $repo->get_typename();
    $repository = new $classname($repo_id, $contextid, array('ajax'=>true, 'name'=>$repo->get_name(), 'type'=>$repo->get_typename()));
} else {
    $err->error = get_string('invalidplugin', 'repository', $repo->get_typename());
    die(json_encode($err));
}

if ($action === 'upload'){
    $audio_format = intval(get_config('voicerecording', 'audio_format'));
    $extension = ($audio_format === 0?'spx':'wav');
    $saveas_filename = trim($saveas_filename). '.' .$extension;
    $result = $repository->upload($saveas_filename, $maxbytes, $itemid);
    echo json_encode($result);
}
