<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A repository plugin to allow user create and upload audio files
 *
 * @since 2.0
 * @package    repository
 * @subpackage voice recording
 * @copyright  2012 Institut Obert de Catalunya
 * @author     Marc Català <mcatala@ioc.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class repository_voicerecording extends repository {
    private $mimetypes = array();
    private $extension = '';

    /**
     * Initialize context and options
     * @param integer $repositoryid repository instance id
     * @param integer|object a context id or context object
     * @param array $options repository options
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array(), $readonly = 0) {
        global $PAGE;
        parent::__construct($repositoryid, $context, $options);
        // Load nanogong script
        $PAGE->requires->js('/repository/voicerecording/nanogong.js');
        // Load voicerecording strings
        $PAGE->requires->string_for_js('nofilename', 'repository_voicerecording');
        $PAGE->requires->string_for_js('norecorder', 'repository_voicerecording');
        $PAGE->requires->string_for_js('norecording', 'repository_voicerecording');
        $PAGE->requires->string_for_js('submitfail', 'repository_voicerecording');
    }

    public static function type_config_form($mform, $classname = 'repository') {
        parent::type_config_form($mform);
        $audioformat = array(
                get_string('audio_speex', 'repository_voicerecording'),
                get_string('audio_imaadpcm', 'repository_voicerecording')
        );
        $samplingrate = array(
                get_string('rate_poor', 'repository_voicerecording'),
                get_string('rate_low', 'repository_voicerecording'),
                get_string('rate_medium', 'repository_voicerecording'),
                get_string('rate_normal', 'repository_voicerecording'),
                get_string('rate_high', 'repository_voicerecording'),
                get_string('rate_excellent', 'repository_voicerecording')
        );

        $mform->addElement('select', 'audio_format', get_string('audio_format', 'repository_voicerecording'), $audioformat);
        $mform->setDefault('audio_format', 0);
        $mform->addElement('select', 'sampling_rate', get_string('sampling_rate', 'repository_voicerecording'), $samplingrate);
        $mform->setDefault('sampling_rate', 2);
    }

    public static function get_type_option_names() {
        return array_merge(parent::get_type_option_names(), array('audio_format', 'sampling_rate'));
    }

    /**
     * Print a voicerecording form
     * @return array
     */
    public function print_login() {
        return $this->get_listing();
    }

    /**
     * Process uploaded file
     * @return array|bool
     */
    public function upload($saveasfilename, $maxbytes, $itemid = 0) {
        global $USER, $CFG, $SESSION;

        if (isset($SESSION->reponanogong)) {
            return $SESSION->reponanogong;
        }

        $types = optional_param_array('accepted_types', '*', PARAM_RAW);

        if ((is_array($types) and in_array('*', $types)) or $types == '*') {
            $this->mimetypes = '*';
        } else {
            foreach ($types as $type) {
                $this->mimetypes[] = mimeinfo('type', $type);
            }
        }

        $record = new stdClass();
        $record->filearea = 'draft';
        $record->component = 'user';
        $record->filepath = optional_param('savepath', '/', PARAM_PATH);
        $record->itemid   = $itemid;
        $record->license  = optional_param('license', $CFG->sitedefaultlicense, PARAM_TEXT);
        $record->author   = optional_param('author', '', PARAM_TEXT);

        $context = context_user::instance($USER->id);
        $elname = 'repo_voicerecording_file';

        $fs = get_file_storage();
        $sm = get_string_manager();

        if ($record->filepath !== '/') {
            $record->filepath = file_correct_filepath($record->filepath);
        }

        if (!isset($_FILES[$elname])) {
            throw new moodle_exception('nofile');
        }
        if (!empty($_FILES[$elname]['error'])) {
            switch ($_FILES[$elname]['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    throw new moodle_exception('voicerecording_error_ini_size', 'repository_voicerecording');
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    throw new moodle_exception('voicerecording_error_form_size', 'repository_voicerecording');
                    break;
                case UPLOAD_ERR_PARTIAL:
                    throw new moodle_exception('voicerecording_error_partial', 'repository_voicerecording');
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new moodle_exception('voicerecording_error_no_file', 'repository_voicerecording');
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    throw new moodle_exception('voicerecording_error_no_tmp_dir', 'repository_voicerecording');
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    throw new moodle_exception('voicerecording_error_cant_write', 'repository_voicerecording');
                    break;
                case UPLOAD_ERR_EXTENSION:
                    throw new moodle_exception('voicerecording_error_extension', 'repository_voicerecording');
                    break;
                default:
                    throw new moodle_exception('nofile');
            }
        }

        // scan the files, throws exception and deletes if virus found
        // this is tricky because clamdscan daemon might not be able to access the files
        $permissions = fileperms($_FILES[$elname]['tmp_name']);
        @chmod($_FILES[$elname]['tmp_name'], $CFG->filepermissions);
        self::antivir_scan_file($_FILES[$elname]['tmp_name'], $_FILES[$elname]['name'], true);
        $sourcefield = $this->get_file_source_info($_FILES[$elname]['name']);
        $record->source = self::build_source_field($sourcefield);
        @chmod($_FILES[$elname]['tmp_name'], $permissions);

        if (empty($saveasfilename)) {
            $record->filename = clean_param($_FILES[$elname]['name'], PARAM_FILE);
        } else {
            $ext = '';
            $match = array();
            $filename = clean_param($_FILES[$elname]['name'], PARAM_FILE);
            if (preg_match('/\.([a-z0-9]+)$/i', $filename, $match)) {
                if (isset($match[1])) {
                    $ext = $match[1];
                }
            }
            $ext = !empty($ext) ? $ext : '';
            if (preg_match('#\.(' . $ext . ')$#i', $saveasfilename)) {
                // saveas filename contains file extension already
                $record->filename = $saveasfilename;
            } else {
                $record->filename = $saveasfilename . '.' . $ext;
            }
        }

        if ($this->mimetypes != '*') {
            // check filetype
            $filemimetype = mimeinfo('type', $_FILES[$elname]['name']);
            if (!in_array($filemimetype, $this->mimetypes)) {
                if ($sm->string_exists($filemimetype, 'mimetypes')) {
                    $filemimetype = get_string($filemimetype, 'mimetypes');
                }
                throw new moodle_exception('invalidfiletype', 'repository', '', $filemimetype);
            }
        }

        if (empty($record->itemid)) {
            $record->itemid = 0;
        }

        if (($maxbytes !== -1) && (filesize($_FILES[$elname]['tmp_name']) > $maxbytes)) {
            throw new file_exception('maxbytes');
        }

        if (file_is_draft_area_limit_reached($record->itemid, FILE_AREA_MAX_BYTES_UNLIMITED, filesize($_FILES[$elname]['tmp_name']))) {
            throw new file_exception('maxareabytes');
        }

        $record->contextid = $context->id;
        $record->userid    = $USER->id;

        if (repository::draftfile_exists($record->itemid, $record->filepath, $record->filename)) {
            $existingfilename = $record->filename;
            $unusedfilename = repository::get_unused_filename($record->itemid, $record->filepath, $record->filename);
            $record->filename = $unusedfilename;
            $storedfile = $fs->create_file_from_pathname($record, $_FILES[$elname]['tmp_name']);
            $event = array();
            $event['event'] = 'fileexists';
            $event['newfile'] = new stdClass;
            $event['newfile']->filepath = $record->filepath;
            $event['newfile']->filename = $unusedfilename;
            $event['newfile']->url = moodle_url::make_draftfile_url($record->itemid, $record->filepath, $unusedfilename)->out();

            $event['existingfile'] = new stdClass;
            $event['existingfile']->filepath = $record->filepath;
            $event['existingfile']->filename = $existingfilename;
            $event['existingfile']->url      = moodle_url::make_draftfile_url($record->itemid, $record->filepath, $existingfilename)->out(false);
            $SESSION->reponanogong = clone($event);
            return $event;
        } else {
            $storedfile = $fs->create_file_from_pathname($record, $_FILES[$elname]['tmp_name']);
            $SESSION->reponanogong = array(
                'url' => moodle_url::make_draftfile_url($record->itemid, $record->filepath, $record->filename)->out(false),
                'id' => $record->itemid,
                'file' => $record->filename);
            return $SESSION->reponanogong;
        }
    }

    /**
     * Return voicerecording form
     * @return array
     */
    public function get_listing($path = '', $page = '') {
        global $CFG;
        $ret = array();
        $ret['nologin']  = true;
        $ret['nosearch'] = true;
        $ret['norefresh'] = true;
        $ret['list'] = array();
        $ret['dynload'] = false;
        $ret['upload'] = array('label'=>get_string('attachment', 'repository'), 'id'=>'repo-form');
        $ret['allowcaching'] = true; // indicates that result of get_listing() can be cached in filepicker.js
        return $ret;
    }

    public function get_upload_template() {
        global $CFG, $USER, $OUTPUT;
        $html = '';

        $context = context_user::instance($USER->id);

        $samplingrates = array(8000, 11025, 16000, 22050, 32000, 44100);
        $audioformats = array('Speex', 'ImaADPCM');

        $audioformat = intval(get_config('voicerecording', 'audio_format'));
        $samplingrate = intval(get_config('voicerecording', 'sampling_rate'));

        $this->extension = ($audioformat === 0 ? 'spx' : 'wav');

        $html .= html_writer::start_tag('div', array('class' => 'fp-upload-form'));
        $html .= html_writer::start_tag('div', array('class' => 'fp-content-center'));
        $html .= html_writer::start_tag('form', array('class' => 'form-horizontal'));
        $html .= html_writer::start_tag('div', array('class' => 'control-group voicerecording-applet'));
        $html .= '<object type="application/x-java-applet" width="180" height="40" id="voicerecording">'.
                         '<param name="code" value="gong.NanoGong"/>'.
                         '<param name="archive" value="' . $CFG->wwwroot . '/repository/voicerecording/nanogong.jar"/>'.
                         '<param name="ShowRecordButton" value="true" />'.
                         '<param name="ShowSaveButton" value="true" />'.
                         '<param name="SamplingRate" value="'.$samplingrates[$samplingrate].'"/>'.
                         '<param name="AudioFormat" value="'.$audioformats[$audioformat].'" />'.
                '</object>';
        $html .= html_writer::end_tag('div');
        $html .= html_writer::start_tag('div', array('class' => 'fp-saveas control-group'));
        $html .= html_writer::label(get_string('saveas', 'repository'), 'itemid', true, array('class' => 'required control-label'));
        $html .= html_writer::start_tag('div', array('class' => 'controls'));
        $params = array(
            'id' => 'filenanoname',
            'type' => 'text',
            'class' => 'voicerecording-input',
        );
        $html .= html_writer::empty_tag('input', $params);
        $html .= html_writer::end_tag('div');
        $html .= html_writer::start_tag('span', array('class' => 'fp-file'));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'value' => '1'));
        $html .= html_writer::end_tag('span');
        $html .= html_writer::start_tag('span', array('class' => 'fp-setauthor'));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden'));
        $html .= html_writer::end_tag('span');
        $html .= html_writer::start_tag('span', array('class' => 'fp-setlicense'));
        $html .= html_writer::tag('select', '', array('class' => 'hidden'));
        $html .= html_writer::end_tag('span');
        $html .= html_writer::end_tag('div');

        $fileuploaderphp = new moodle_url('/repository/voicerecording/ngupload.php',
                        array( 'action'  => 'upload',
                               'ctx_id'  => $context->id,
                               'repo_id' => $this->id,
                               'author'  => fullname($USER),
                               'sesskey' => sesskey()));
        $fileuploaderphp = htmlspecialchars_decode($fileuploaderphp);

        $html .= html_writer::end_tag('form');
        $html .= html_writer::start_tag('div', array('class' => 'mdl-align'));
        $params = array(
            'id' => 'btn-voicerecording',
            'onclick' => 'event.preventDefault();uploadNanogongRecording(\'voicerecording\',\'' . $fileuploaderphp . '\');',
            'class' => 'btn fp-upload-btn btn-primary',
            'value' => 'Submit',
            'type' => 'button',
        );
        $html .= html_writer::tag('button', get_string('submit'), $params);

        return $html;
    }

    /**
     *
     * @return array
     */
    public function supported_filetypes() {
        return array('audio/ogg', 'audio/wav');
    }

    /**
     * supported return types
     * @return int
     */
    public function supported_returntypes() {
        return FILE_INTERNAL;
    }
}
