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

        // Pattern for mediaplugin_videojs wrapped YouTube videos.
        // This must come first to match the complete wrapper before matching inner elements.
        // Matches complete div wrapper and captures the full YouTube URL with parameters.
        $regexmediaplugindiv = '/('
            . '(<div[^>]*class="[^"]*mediaplugin[^"]*"[^>]*>.*?'
            . '<video[^>]*>.*?'
            . '(?:https?:\/\/)?(?:www\.)?(?:'
            . 'youtube(?:-nocookie)?\.com\/(?:watch\?v=|embed\/)'
            . '|youtu\.be\/'
            . ')([\w\d\-_]+)([^"&]*)'
            . '.*?<\/video>.*?<\/div>)'
            . ')/s';

        $regexyoutube = '/('
            . '(<video[^>]+><source[^>]*src="(((http|https):\/\/){0,1}(\bwww\.youtube\b(\b\-nocookie\b)?\b\.com\b)'
            . '(\/watch\?v=)([\w\d\-]+)([\w@\?^=%&\/~+#\-;]+)?)"[^>]*>.*?<\/video>)'
            . '|(<iframe[^>]*src="((http|https):\/\/{0,1}(\bwww\.youtube\b(\b\-nocookie\b)?\b\.com\/embed\/\b)'
            . '([\w\d\-]+)([\w@\?^=%&\/~+#\-;]+)?)"[^>]*>.*?<\/iframe>)'
            . '|(<iframe[^>]*src="((http|https):\/\/{0,1}(\bwww\.youtube\b(\b\-nocookie\b)?\b\.com\b)(\/watch\?v=)'
            . '([\w\d\-]+)([\w@\?^=%&\/~+#\-;]+)?)"[^>]*>.*?<\/iframe>)'
            . '|(<a[^>]*href="(((http|https):\/\/){0,1}(\bwww\.youtube\b(\b\-nocookie\b)?\b\.com\b)(\/watch\?v=)'
            . '([\w\d\-]+)([\w@\?^=%&\/~+#\-;]+)?)"[^>]*>[^<]+<\/a>)'
            . '|(<a[^>]*href="(((http|https):\/\/){0,1}(\bwww\.youtube\b(\b\-nocookie\b)?\b\.com\b)(\/embed\/)'
            . '([\w\d\-]+)([\w@\?^=%&\/~+#\-;]+)?)"[^>]*>[^<]+<\/a>)'
            // Plain URL patterns (not inside HTML tags).
            . '|(?<!["\'>\/])((?<!</source>)\s*)((https?:\/\/)(www\.youtube(-nocookie)?\.com)(\/watch\?v=)([\w\d\-]+)([\w@\?^=%&\/~+#\-;]*)?)(?![^<]*<\/video>)'
            . '|(?<!["\'>\/])((?<!</source>)\s*)((https?:\/\/)(www\.youtube(-nocookie)?\.com)(\/embed\/)([\w\d\-]+)([\w@\?^=%&\/~+#\-;]*)?)(?![^<]*<\/video>)'
            . ')/s';
        $regexyoutubeshorturl = '/('
            . '(<a[^>]*href="((http|https):\/\/youtu\.be\/([\w\d\-_]+)([\w@\?^=%&\/~+#\-]+)?)"[^>]*>[^<]+<\/a>)'
            . '|(<video[^>]+><source[^>]*src="((http|https):\/\/youtu\.be\/([\w\d\-_]+)([\w@\?^=%&\/~+#\-]+)?)"[^>]*>.*?<\/video>)'
            // Plain short URL pattern (not inside HTML tags).
            . '|(?<!["\'>\/])((https?:\/\/)(youtu\.be)\/([\w\d\-_]+)([\w@\?^=%&\/~+#\-]*)?)(?![^<]*<\/video>)'
            . ')/s';

        $patternsandcallbacks = [
            $regexmediaplugindiv => [$this, 'mediaplugin_div_callback'],
            $regexyoutube => [$this, 'youtube_callback'],
            $regexyoutubeshorturl => [$this, 'youtube_shorturl_callback'],
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
     * Callback to replace mediaplugin_videojs div wrapper with DSGVO conform style.
     *
     * @param array $match Regex match array
     * @return string Replaced HTML content
     */
    protected function mediaplugin_div_callback(array $match): string {
        $hasuseraccepted = $this->get_hasuseraccepted();
        $styles = $this->get_style_attributs();

        // Video ID is at index 3 in the regex pattern.
        if (empty($match[3])) {
            // Fallback: return original match if we can't extract the video ID.
            return $match[0];
        }

        $vid = $match[3];
        $params = [];

        // Try to extract the complete URL with query parameters from the match.
        // The URL can be in various formats within the src attribute or JSON src property.
        // Match everything between src=&quot; or src&quot;:&quot; and closing &quot;, including &amp; encoded parameters.
        if (preg_match('/src(?:=|&quot;:)&quot;(https?:\/\/[^"]+?)&quot;/', $match[0], $srcmatch)) {
            // Decode HTML entities (&quot; -> ", &amp; -> &) to get proper URL.
            $url = html_entity_decode($srcmatch[1], ENT_QUOTES | ENT_HTML5);
            $params = parse_url($url);
        } else if (!empty($match[4])) {
            // Fallback: use captured query string from regex (index 4).
            $querystring = html_entity_decode($match[4], ENT_QUOTES | ENT_HTML5);
            if (!empty($querystring)) {
                // Remove leading '?' or '&' if present.
                $querystring = ltrim($querystring, '?&');
                $params['query'] = $querystring;
            }
        }

        $urlparam = self::build_url_querystring($params);
        array_push($this->youtubevideoids, $vid);
        $wrapper = $this->render_two_click_version_youtube($vid, $hasuseraccepted, $urlparam['paramarr'], $styles);

        return $wrapper;
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

        $vid = null;
        $params = [];

        // Extract video ID and URL from the match array
        // Corrected pattern indices after recounting all capture groups:
        // Pattern 1: <video> tag with /watch?v= - video ID at index 9, URL at index 3
        // Pattern 2: <iframe> with /embed/ - video ID at index 16, URL at index 12
        // Pattern 3: <iframe> with /watch?v= - video ID at index 24, URL at index 19
        // Pattern 4: <a> tag with /watch?v= - video ID at index 33, URL at index 27
        // Pattern 5: <a> tag with /embed/ - video ID at index 42, URL at index 36
        // Pattern 6: Plain URL with /watch?v= - video ID at index 49, URL at index 44
        // Pattern 7: Plain URL with /embed/ - video ID at index 56, URL at index 51

        $patterns = [
            ['vidIdx' => 9, 'urlIdx' => 3],    // video tag
            ['vidIdx' => 16, 'urlIdx' => 12],  // iframe with /embed/
            ['vidIdx' => 24, 'urlIdx' => 19],  // iframe with /watch?v=
            ['vidIdx' => 33, 'urlIdx' => 27],  // a tag with /watch?v=
            ['vidIdx' => 42, 'urlIdx' => 36],  // a tag with /embed/
            ['vidIdx' => 49, 'urlIdx' => 44],  // plain URL with /watch?v=
            ['vidIdx' => 56, 'urlIdx' => 51],  // plain URL with /embed/
        ];

        foreach ($patterns as $pattern) {
            if (isset($match[$pattern['vidIdx']]) && !empty($match[$pattern['vidIdx']])) {
                $vid = $match[$pattern['vidIdx']];
                if (isset($match[$pattern['urlIdx']]) && !empty($match[$pattern['urlIdx']])) {
                    $params = parse_url($match[$pattern['urlIdx']]);
                }
                break;
            }
        }

        if (empty($vid)) {
            // Fallback: return original match if we can't extract the video ID
            return $match[0];
        }

        if (empty($params)) {
            $params = [];
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

        $vid = null;
        $params = [];

        // Pattern group indices for short URLs:
        // Group 2: <a> tag, URL at index 3, video ID at index 5
        // Group 7: <video> tag, URL at index 8, video ID at index 10
        // Group 12: Plain URL, URL at index 12, video ID at index 15

        $patterns = [
            ['vidIdx' => 5, 'urlIdx' => 3],   // a tag
            ['vidIdx' => 10, 'urlIdx' => 8],   // video tag
            ['vidIdx' => 15, 'urlIdx' => 12],  // plain URL
        ];

        foreach ($patterns as $pattern) {
            if (!empty($match[$pattern['vidIdx']])) {
                $vid = $match[$pattern['vidIdx']];
                if (!empty($match[$pattern['urlIdx']])) {
                    $params = parse_url($match[$pattern['urlIdx']]);
                }
                break;
            }
        }

        if (empty($vid)) {
            // Fallback: return original match if we can't extract the data
            return $match[0];
        }

        if (empty($params)) {
            $params = [];
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
