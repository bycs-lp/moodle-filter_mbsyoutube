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
 * Displays the video wrapper of YouTube Videos in a new browser tab.
 *
 * @copyright 2020 ISB Bayern
 * @author    Peter Mayer
 * @package   filter_mbsyoutube
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$PAGE->set_url('/filter/mbsyoutube/video_popup.php');

require_login();

define('YOUTUBENOCOOKIEURL', "https://www.youtube-nocookie.com/watch?v=");

$PAGE->set_pagelayout('popup');
$PAGE->set_context(context_system::instance());

$vid = required_param('vid', PARAM_ALPHANUMEXT);
$timestart  = optional_param('timestart', 0, PARAM_INT);
$timeend = optional_param('timeend', 0, PARAM_INT);

echo $OUTPUT->header();

echo $OUTPUT->container_start();
$url = YOUTUBENOCOOKIEURL . $vid;
echo format_text(html_writer::tag('div', $url));
echo $OUTPUT->container_end();

echo $OUTPUT->footer();
