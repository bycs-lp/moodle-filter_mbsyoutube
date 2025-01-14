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
 * Language definition
 *
 * @package    filter_mbsyoutube
 * @copyright  2020 Peter Mayer, ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cachedef_mbsexternalsourceaccept'] = 'Speichert die Zustimmung, dass der User mit der Weitergabe der IP-Adresse an '
    . 'YouTube einverstanden ist, im Cache.';
$string['filtername'] = 'YouTube Einwilligungsdialog rendern';
$string['mbsopenpopup'] = 'Videoplayer öffnen';
$string['mbstwoclickboxtext'] = '<strong>Datenschutzhinweis</strong>'
    . '<br />Sobald das Video abgespielt wird, werden an YouTube persönliche <a href="https://policies.google.com/privacy" '
    . 'target="_blank" style="color:#e3e3e3 !important;">Daten</a> wie die IP-Adresse übermittelt.';
$string['mbswatchvideo'] = 'Video trotzdem ansehen ✓';
$string['mbsyoutube_twoclickacceptancebuttonmsgtext'] = 'Beschriftung des Einwilligungsbuttons in einer Nutzernachricht';
$string['mbsyoutube_twoclickacceptancebuttonmsgtext_desc'] = 'Beschriftung des Einwilligungsbuttons zum Abspielen des Videos in '
. 'einer Nutzernachricht';
$string['mbsyoutube_twoclickacceptancebuttontext'] = 'Beschriftung des Einwilligungsbuttons';
$string['mbsyoutube_twoclickacceptancebuttontext_desc'] = 'Beschriftung des Einwilligungsbuttons zum Abspielen des Videos';
$string['mbsyoutube_twoclickbackground'] = 'Hintergrundbild des Einwilligungsdialogs';
$string['mbsyoutube_twoclickbackground_desc'] = 'Laden Sie hier ein Bild hoch, das als Hintergrundbild '
    . 'für den Einwilligungsdialog dienen soll.';
$string['mbsyoutube_twoclicklogo'] = 'Logo im Einwilligungsdialog';
$string['mbsyoutube_twoclicklogo_desc'] = 'Laden Sie hier ein Bild hoch, das als Logo auf '
    . 'dem Einwilligungsdialog verwendet werden soll.';
$string['mbsyoutube_twoclickmessage'] = 'Datenschutzhinweis im Einwilligungsdialog';
$string['mbsyoutube_twoclickmessage_desc'] = 'Nachricht mit Datenschutzhinweis auf dem Einwilligungsdialog.';
$string['privacy:metadata'] = 'Das mbsYouTube Plugin speichert keine personenbezogenen Daten.';
