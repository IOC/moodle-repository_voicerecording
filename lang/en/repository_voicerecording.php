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
 * Strings for component 'repository_voicerecording'
 *
 * @package   repository_voicerecording
 * @copyright Marc Català  <mcatala@ioc.cat>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['configplugin'] = 'Configuration for Voice recording plugin';
$string['pluginname_help'] = 'Voice recording';
$string['pluginname'] = 'Voice recording';
$string['voicerecording:view'] = 'Use voice recording repository';
$string['voicerecording_error_ini_size'] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
$string['voicerecording_error_form_size'] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
$string['voicerecording_error_partial'] = 'The uploaded file was only partially uploaded.';
$string['voicerecording_error_no_file'] = 'No file was uploaded.';
$string['voicerecording_error_no_tmp_dir'] = 'PHP is missing a temporary folder.';
$string['voicerecording_error_cant_write'] = 'Failed to write file to disk.';
$string['voicerecording_error_extension'] = 'A PHP extension stopped the file upload.';
$string['nofilename'] = 'Filename empty!';
$string['norecorder'] = 'Recorder not found';
$string['norecording'] = 'No recording found';
$string['audio_format'] = 'Audio format';
$string['audio_imaadpcm'] = 'ImaADPCM';
$string['audio_speex'] = 'Speex';
$string['rate_poor'] = 'Poor quality';
$string['rate_low'] = 'Low quality';
$string['rate_medium'] = 'Medium quality';
$string['rate_normal'] = 'Normal quality';
$string['rate_high'] = 'High quality';
$string['rate_excellent'] = 'Excellent quality';
$string['sampling_rate'] = 'Sampling rate';
$string['submitfail'] = 'Failed to submit the voice recording';