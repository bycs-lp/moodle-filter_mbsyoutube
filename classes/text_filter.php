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

namespace filter_mbsyoutube;

use core_cache\cache;
use moodle_url;
use context_system;
use stdClass;

/**
 * Filter class mbsyoutube.
 *
 * @package    filter_mbsyoutube
 * @copyright  2020 Peter Mayer, ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_filter extends \core_filters\text_filter {

    /**
     * @var array $youtubevideoids Array of all YouTube Video IDs of course page.
     */
    private $youtubevideoids = [];

    /**
     * @var int $courseid Course ID of the recent course.
     */
    private $courseid;

    /**
     * Setup page with filter requirements and other prepare stuff.
     *
     * Override this method if the filter needs to setup page
     * requirements or needs other stuff to be executed.
     *
     * Note this method is invoked from {@see setup_page_for_filters()}
     * for each piece of text being filtered, so it is responsible
     * for controlling its own execution cardinality.
     *
     * @param \moodle_page $page the page we are going to add requirements to.
     * @param \context $context the context which contents are going to be filtered.
     * @since Moodle 2.3
     */
    public function setup($page, $context) {
        $this->courseid = $page->course->id;
        if (!$this->get_hasuseraccepted()) {
            $page->requires->js_call_amd('filter_mbsyoutube/sethasuseraccepted', 'init', ['courseid' => $this->courseid]);
        } else {
            $url = new moodle_url('https://www.youtube.com/iframe_api');
            $page->requires->js($url);
            $page->requires->js_call_amd('filter_mbsyoutube/youtube_api', 'init');
        }
    }

    /**
     * Filter the text and replace links to youtube.com with an DSGVO conform style.
     *
     * Please note that we replace links, urls AND iframes. In order to support all
     * kinds of YouTube embedding.
     *
     * @param string $text some HTML content
     * @param array $options options passed to the filters
     * @return string the HTML content after the filtering has been applied
     */
    public function filter($text, array $options = []) {

        if (!is_string($text) || empty($text)) {
            return $text;
        }

        // When adding a new regex command, there must be added a new if clause in the callback function, too.
        $regexyoutube = '/('
            . '((<video[^>]+><source[^>]?src=")(((http|https):\/\/){0,1}(\bwww\.youtube\b(\b\-nocookie\b)?\b\.com\b)'
            . '(\/watch\?v=)([\w\d\-]+)([\w@\?^=%&\/~+#\-;]+)?)'
            . '(">[^<]+<\/video>)?)'
            . '|((<a[^>]?href=")?(((http|https):\/\/){0,1}(\bwww\.youtube\b(\b\-nocookie\b)?\b\.com\b)(\/watch\?v=)'
            . '([\w\d\-]+)([\w@\?^=%&\/~+#\-;]+)?)("?[^<]+<\/a>)?)'
            . '|(<iframe(.*)src="((http|https):\/\/{0,1}(\bwww\.youtube\b(\b\-nocookie\b)?\b\.com\/embed\/\b)'
            . '([\w\d\-]+)([\w@\?^=%&\/~+#\-;]+)?)"(.*)>(.*)<\/iframe>)'
            . '|(<a[^>]?href=")?(((http|https):\/\/){0,1}(\bwww\.youtube\b(\b\-nocookie\b)?\b\.com\b)(\/embed\/)'
            . '([\w\d\-]+)([\w@\?^=%&\/~+#\-;]+)?("?[^<]+<\/a>)?)'
            . '|(<iframe(.*)src="((http|https):\/\/{0,1}(\bwww\.youtube\b(\b\-nocookie\b)?\b\.com\b)(\/watch\?v=)'
            . '([\w\d\-]+)([\w@\?^=%&\/~+#\-;]+)?)"(.*)>(.*)<\/iframe>)'
            . ')/';
        $regexyoutubeshorturl = '/('
            . '((<a[^>]?href=")((http|https):\/\/youtu.be\/([\w\d\-_]+)([\w@\?^=%&\/~+#\-]+)?)"?[^<]+<\/a>)'
            . '|((<video[^>]+><source[^>]?src=")((http|https):\/\/youtu.be\/([\w\d\-_]+)([\w@\?^=%&\/~+#\-]+)?)"?[^<]+<\/video>)'
            . '|((http|https):\/\/youtu.be\/([\w\d\-_]+)([\w@\?^=%&\/~+#\-]+)?)'
            . ')/';

        $patternsandcallbacks = [
            $regexyoutube => "\\filter_mbsyoutube\\text_filter::youtube_callback",
            $regexyoutubeshorturl => "\\filter_mbsyoutube\\text_filter::youtube_shorturl_callback",
        ];

        $newtext = preg_replace_callback_array(
            $patternsandcallbacks,
            $text,
            -1,
            $count
        );

        return $newtext;
    }

    /**
     * Form the html attributes needed for the wrapper.
     * @return string $styles
     */
    protected function get_style_attributs(): string {
        if ($backgroundfile = get_config('filter_mbsyoutube', 'mbsyoutube_two_click_background')) {
            $backgroundurl = moodle_url::make_pluginfile_url(
                context_system::instance()->id,
                'filter_mbsyoutube',
                'background',
                0,
                null,
                $backgroundfile
            );
        } else {
            $backgroundurl = '';
        }
        $styles = 'background-image: url(' . $backgroundurl . ');';
        return $styles;
    }

    /**
     * Get hasuseraccepted from cache.
     *
     * @return mixed $hasuseraccepted  - the data that was associated with the key,
     *                                   or false if the key did not exist.
     */
    protected function get_hasuseraccepted() {
        global $USER;
        $courseid = $this->courseid;
        $cache = cache::make('filter_mbsyoutube', 'mbsexternalsourceaccept');
        return $cache->get($USER->id . "_" . $courseid . "_YouTube");
    }

    /**
     * Callback to set a YouTube url to match DSGVO.
     *
     * @param array $match
     * @return string Url
     */
    protected function youtube_callback(array $match): string {
        $hasuseraccepted = $this->get_hasuseraccepted();
        $styles = $this->get_style_attributs();

        // The following arrays containing all possible offset of the $match array.
        $videoffsets = [10, 21, 30, 41, 51];
        $urloffsets = [4, 15, 26, 35, 46];

        foreach ($videoffsets as $videoffset) {
            if (isset($match[$videoffset])) {
                $vid = $match[$videoffset];
            }
        }
        foreach ($urloffsets as $urloffset) {
            if (isset($match[$urloffset])) {
                $params = parse_url($match[$urloffset]);
            }
        }

        $urlparam = self::build_url_querystring($params);
        array_push($this->youtubevideoids, $vid);
        $iframe = $this->render_two_click_version_youtube($vid, $hasuseraccepted, $urlparam['paramarr'], $styles);

        return $iframe;
    }

    /**
     * Callback to set a YouTube url to match DSGVO from a shorten url.
     *
     * @param array $match
     * @return string $ytwrapper YouTube Video wrapper element.
     */
    protected function youtube_shorturl_callback(array $match): string {

        $hasuseraccepted = $this->get_hasuseraccepted();

        $styles = $this->get_style_attributs();

        // The order of the if clauses has to be the same as the order of the regex commands.
        if (is_string($match[6]) && strlen($match[6]) > 1) {
            $vid = $match[6];
            $params = parse_url($match[4]);
        } else if (is_string($match[12]) && strlen($match[12]) > 1) {
            $vid = $match[12];
            $params = parse_url($match[10]);
        } else if (is_string($match[16]) && strlen($match[16]) > 1) {
            $vid = $match[16];
            $params = parse_url($match[14]);
        }
        $urlparam = self::build_url_querystring($params);

        array_push($this->youtubevideoids, $vid);

        $ytwrapper = $this->render_two_click_version_youtube($vid, $hasuseraccepted, $urlparam['paramarr'], $styles);
        return $ytwrapper;
    }

    /**
     * Generates the two click behaviour from a youtube url.
     *
     * @param string $videoid
     * @param bool $hasuseraccepted True if provider (YouTube) is accepted
     * @param array $urlparam Contains all parameters to post to iframe.
     * @param string $styles
     * @return string HTML markup
     */
    private function render_two_click_version_youtube(
            string $videoid,
            bool $hasuseraccepted = false,
            array $urlparam = [],
            string $styles = ''
    ): string {
        global $OUTPUT;

        $data = new stdClass();
        $data->mbswrapperstyles = $styles;
        $data->videoid = $videoid;

        if (PHPUNIT_TEST) {
            $data->uniqid = 'phpunit';
        } else {
            $data->uniqid = uniqid();
        }

        $data->popupnurl = new moodle_url('/filter/mbsyoutube/video_popup.php', ['vid' => $videoid]);
        $data->mbsplayerdetails = json_encode($urlparam, JSON_UNESCAPED_SLASHES);
        $data->mbstwoclickboxtext = get_string('mbstwoclickboxtext', 'filter_mbsyoutube');
        $data->mbsopenpopup = get_config('filter_mbsyoutube', 'mbsyoutube_two_click_acceptancebuttonmsgtext');
        $data->mbswatchvideo = get_config('filter_mbsyoutube', 'mbsyoutube_two_click_acceptancebuttontext');
        if ($logo = get_config('filter_mbsyoutube', 'mbsyoutube_two_click_logo')) {
            $logourl = moodle_url::make_pluginfile_url(
                context_system::instance()->id,
                'filter_mbsyoutube',
                'logo',
                0,
                null,
                $logo
            );
        } else {
            $logourl = null;
        }
        $data->mebislogourl = $logourl;

        if ($hasuseraccepted) {
            $data->optionacceptedhidden = ' hidden="hidden"';
            $data->optionnotacceptedhidden = "";
        } else {
            $data->optionacceptedhidden = "";
            $data->optionnotacceptedhidden = ' hidden="hidden"';
        }

        return $OUTPUT->render_from_template('filter_mbsyoutube/mbsyoutubetwoclick', $data);
    }

    /**
     * Gets all URL query parameters and returns allowed parameters as url string.
     *
     * @param array $params
     * @return array URL parameters
     */
    private function build_url_querystring(array $params): array {
        global $CFG;
        $preconfparam = [
            'modestbranding' => 1,
            'iv_load_policy' => 3,
            'enablejsapi' => 1,
            'origin' => $CFG->wwwroot,
        ];

        if (isset($params['query'])) {
            $query = html_entity_decode($params['query']);
            $urlparams = [];
            parse_str($query, $urlparams);
            $allowedparams = ['start', 'end', 't'];
            $urlparams = array_intersect_key($urlparams, array_flip($allowedparams));
            $urlparams = array_merge($preconfparam, $urlparams);
            if (isset($urlparams['t'])) {
                $urlparams['start'] = $urlparams['t'];
                unset($urlparams['t']);
            }
            $urlparamret['paramstr'] = "?" . http_build_query($urlparams, '', "&");
            $urlparamret['paramarr'] = $urlparams;
        } else {
            $urlparamret['paramstr'] = "";
            $urlparamret['paramarr'] = $preconfparam;
        }
        return $urlparamret;
    }
}
