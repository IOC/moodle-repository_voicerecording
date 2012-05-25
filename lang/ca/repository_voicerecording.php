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

$string['configplugin'] = 'Configuració del plugin Enregistrament de veu';
$string['pluginname_help'] = 'Enregistrament de veu';
$string['pluginname'] = 'Enregistrament de veu';
$string['voicerecording:view'] = 'Utilitza el repositori d\'enregistrament de veu';
$string['voicerecording_error_ini_size'] = 'La mida del arxiu supera la directiva upload_max_filesize de l\'arxiu php.ini.';
$string['voicerecording_error_form_size'] = 'L\'arxiu pujat supera la directiva MAX_FILE_SIZE especificada en el formulari HTML.';
$string['voicerecording_error_partial'] = 'L\'arxiu s\'ha pujat parcialment.';
$string['voicerecording_error_no_file'] = 'No s\'ha pujat cap arxiu.';
$string['voicerecording_error_no_tmp_dir'] = 'No existeix una carpeta temporal per al PHP.';
$string['voicerecording_error_cant_write'] = 'Error al escriure l\'arxiu a disc.';
$string['voicerecording_error_extension'] = 'Una extensió de PHP ha aturat la pujada de l\'arxiu.';
$string['nofilename'] = 'Nom d\'arxiu buit!';
$string['norecorder'] = 'No s\'ha trobat Nanogong';
$string['norecording'] = 'No s\'ha trobat cap gravació';
$string['audio_format'] = 'Audio format';
$string['audio_imaadpcm'] = 'ImaADPCM';
$string['audio_speex'] = 'Speex';
$string['rate_poor'] = 'Qualitat dolenta';
$string['rate_low'] = 'Qualitat baixa';
$string['rate_medium'] = 'Qualitat mitja';
$string['rate_normal'] = 'Qualitat normal';
$string['rate_high'] = 'Qualitat alta';
$string['rate_excellent'] = 'Qualitat excel·lent';
$string['sampling_rate'] = 'Freqüència de mostreig';
$string['submitfail'] = 'Error en l\'enviament de la gravació de veu';