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

$string['cachedef_mbsexternalsourceaccept'] = 'Caches the consent that the user agrees to share the IP address with YouTube.';
$string['filtername'] = 'Render YouTube consent dialog';
$string['mbsopenpopup'] = 'Open video player';
$string['mbstwoclickboxtext'] = '<strong>Data protection notice</strong>'
    . '<br />As soon as the video is played, personal <a href="https://policies.google.com/privacy" '
. 'target="_blank" style="color:#e3e3e3 !important;">data</a> such as the IP address is transmitted to YouTube';
$string['mbswatchvideo'] = 'Watch video anyway ✓';
$string['mbsyoutube_twoclickacceptancebuttonmsgtext'] = 'Button caption for the consent button in a user message';
$string['mbsyoutube_twoclickacceptancebuttonmsgtext_desc'] = 'Button caption for consent to play video in a user message';
$string['mbsyoutube_twoclickacceptancebuttontext'] = 'Caption of the consent button';
$string['mbsyoutube_twoclickacceptancebuttontext_desc'] = 'Caption of the consent button to play the video';
$string['mbsyoutube_twoclickbackground'] = 'Background image of the consent dialog';
$string['mbsyoutube_twoclickbackground_desc'] = 'Upload an image here to serve as the background image for the consent dialog.';
$string['mbsyoutube_twoclicklogo'] = 'Logo in consent dialog';
$string['mbsyoutube_twoclicklogo_desc'] = 'Upload an image here to be used as the logo on the consent dialog.';
$string['mbsyoutube_twoclickmessage'] = 'Data protection notice in the consent dialog';
$string['mbsyoutube_twoclickmessage_desc'] = 'Message with data privacy notice on consent dialog.';
$string['privacy:metadata'] = 'The mbsYouTube plugin does not store any personal data.';
