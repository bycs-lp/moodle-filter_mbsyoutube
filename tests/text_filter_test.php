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

/**
 * Unit tests for the mbsyoutube text filter.
 *
 * @package filter_mbsyoutube
 * @category test
 * @copyright 2019 Franziska Hübler, 2019 Peter Mayer, ISB Bayern
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \filter_mbsyoutube\text_filter
 */
final class text_filter_test extends \advanced_testcase {

    /**
     * Create a text_filter instance with filter enabled on a fresh course context.
     *
     * @return text_filter
     */
    private function create_filter(): text_filter {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        \core\plugininfo\media::set_enabled_plugins('');

        $filterobject = new \stdClass();
        $filterobject->filter = 'mbsyoutube';
        $filterobject->contextid = $context->id;
        $filterobject->active = 1;
        $filterobject->sortorder = 0;
        $DB->insert_record('filter_active', $filterobject);

        return new text_filter($context, []);
    }

    /**
     * Build the default expected JSON params string (no extra query params).
     *
     * @return string
     */
    private static function default_params_json(): string {
        global $CFG;
        return '{"modestbranding":1,"iv_load_policy":3,"enablejsapi":1,"origin":"' . $CFG->wwwroot . '"}';
    }

    /**
     * Build expected JSON params string with extra allowed params.
     *
     * @param array $extra Associative array of extra params e.g. ['start' => '15'].
     * @return string
     */
    private static function params_json_with(array $extra): string {
        global $CFG;
        $base = [
            'modestbranding' => 1,
            'iv_load_policy' => 3,
            'enablejsapi' => 1,
            'origin' => $CFG->wwwroot,
        ];
        return json_encode(array_merge($base, $extra), JSON_UNESCAPED_SLASHES);
    }

    /**
     * Wrap a YouTube HTML snippet with surrounding paragraph text.
     *
     * @param string $innerhtml The YouTube-containing HTML.
     * @return string
     */
    private static function wrap_in_paragraphs(string $innerhtml): string {
        return '<p>YouTube eingefügt<br>' . $innerhtml . '</p><p>Das ist das Ende!</p>';
    }

    /**
     * Data provider for single YouTube URL filter tests without extra query parameters.
     *
     * Each case provides: a description (key), the input HTML snippet (without surrounding paragraphs),
     * and the expected video ID.
     *
     * @return array
     */
    public static function youtube_url_provider(): array {
        return [
            // <a> tag variations.
            'a tag - youtube.com/watch' => [
                'input' => '<a href="https://www.youtube.com/watch?v=qcQ6x123KwU">Link zum Video</a>',
                'videoid' => 'qcQ6x123KwU',
            ],
            'a tag - youtube-nocookie.com/watch' => [
                'input' => '<a href="https://www.youtube-nocookie.com/watch?v=qcQ6x123KwU">Link zum Video</a>',
                'videoid' => 'qcQ6x123KwU',
            ],
            'a tag - youtube.com/embed' => [
                'input' => '<a href="https://www.youtube.com/embed/qcQ6x123KwU">Link zum Video</a>',
                'videoid' => 'qcQ6x123KwU',
            ],
            'a tag - youtube-nocookie.com/embed' => [
                'input' => '<a href="https://www.youtube-nocookie.com/embed/qcQ6x123KwU">Link zum Video</a>',
                'videoid' => 'qcQ6x123KwU',
            ],
            'a tag - youtu.be short URL' => [
                'input' => '<a href="https://youtu.be/qcQ6x123KwU">Link zum Video</a>',
                'videoid' => 'qcQ6x123KwU',
            ],

            // Plain text URL variations.
            'plain text - youtube.com/watch' => [
                'input' => 'https://www.youtube.com/watch?v=qcQ6x123KwU',
                'videoid' => 'qcQ6x123KwU',
            ],
            'plain text - youtube-nocookie.com/watch' => [
                'input' => 'https://www.youtube-nocookie.com/watch?v=qcQ6x123KwU',
                'videoid' => 'qcQ6x123KwU',
            ],
            'plain text - youtube.com/embed' => [
                'input' => 'https://www.youtube.com/embed/qcQ6x123KwU',
                'videoid' => 'qcQ6x123KwU',
            ],
            'plain text - youtube-nocookie.com/embed' => [
                'input' => 'https://www.youtube-nocookie.com/embed/qcQ6x123KwU',
                'videoid' => 'qcQ6x123KwU',
            ],
            'plain text - youtu.be short URL' => [
                'input' => 'https://youtu.be/qcQ6x123KwU',
                'videoid' => 'qcQ6x123KwU',
            ],

            // <iframe> variations.
            'iframe - youtube.com/embed' => [
                'input' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/qcQ6x123KwU"'
                    . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
                    . ' picture-in-picture" allowfullscreen></iframe>',
                'videoid' => 'qcQ6x123KwU',
            ],
            'iframe - youtube-nocookie.com/embed' => [
                'input' => '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/qcQ6x123KwU"'
                    . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
                    . ' picture-in-picture" allowfullscreen></iframe>',
                'videoid' => 'qcQ6x123KwU',
            ],
            'iframe - youtube.com/watch' => [
                'input' => '<iframe width="560" height="315" src="https://www.youtube.com/watch?v=qcQ6x123KwU"'
                    . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
                    . ' picture-in-picture" allowfullscreen></iframe>',
                'videoid' => 'qcQ6x123KwU',
            ],
            'iframe - youtube-nocookie.com/watch' => [
                'input' => '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/watch?v=qcQ6x123KwU"'
                    . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
                    . ' picture-in-picture" allowfullscreen></iframe>',
                'videoid' => 'qcQ6x123KwU',
            ],

            // <video> tag variations (TinyMCE "Video" tab output).
            'video tag - youtube.com/watch with inner text URL' => [
                'input' => '<video controls="true"><source src="https://www.youtube.com/watch?v=qcQ6x123KwU">'
                    . ' https://www.youtube.com/watch?v=qcQ6x123KwU</video>',
                'videoid' => 'qcQ6x123KwU',
            ],
            'video tag - youtu.be short URL with inner text URL' => [
                'input' => '<video controls="true"><source src="https://youtu.be/qcQ6x123KwU">'
                    . ' https://youtu.be/qcQ6x123KwU</video>',
                'videoid' => 'qcQ6x123KwU',
            ],
        ];
    }

    /**
     * Test that all YouTube URL formats produce the two-click wrapper with correct video ID and default params.
     *
     * @dataProvider youtube_url_provider
     * @covers ::filter
     * @param string $input The YouTube HTML snippet (will be wrapped in paragraphs).
     * @param string $videoid The expected video ID.
     */
    public function test_filter_replaces_youtube_url(string $input, string $videoid): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $filter = $this->create_filter();
        $html = self::wrap_in_paragraphs($input);
        $filtered = $filter->filter($html);

        // Two-click wrapper must be present.
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringContainsString('id="yt___phpunit___' . $videoid . '"', $filtered);
        $this->assertStringContainsString(self::default_params_json(), $filtered);

        // Surrounding text must be preserved.
        $this->assertStringContainsString('<p>YouTube eingefügt<br>', $filtered);
        $this->assertStringContainsString('<p>Das ist das Ende!</p>', $filtered);
    }

    /**
     * Data provider for YouTube URL filter tests with query parameters (start, end, t).
     *
     * @return array
     */
    public static function youtube_url_with_params_provider(): array {
        return [
            // Start parameter (t= shorthand).
            'plain text - youtu.be?t=15' => [
                'input' => 'https://youtu.be/qcQ6x123KwU?t=15',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['start' => '15'],
            ],
            'plain text - youtu.be?start=15' => [
                'input' => 'https://youtu.be/qcQ6x123KwU?start=15',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['start' => '15'],
            ],
            'iframe - youtube.com/watch?v=...&start=15' => [
                'input' => '<iframe width="560" height="315"'
                    . ' src="https://www.youtube.com/watch?v=qcQ6x123KwU&start=15"'
                    . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
                    . ' picture-in-picture" allowfullscreen></iframe>',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['start' => '15'],
            ],
            'iframe - youtube-nocookie.com/watch?v=...&start=15' => [
                'input' => '<iframe width="560" height="315"'
                    . ' src="https://www.youtube-nocookie.com/watch?v=qcQ6x123KwU&start=15"'
                    . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
                    . ' picture-in-picture" allowfullscreen></iframe>',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['start' => '15'],
            ],
            'video tag - youtube.com/watch?v=...&start=15' => [
                'input' => '<video controls="true"><source src="https://www.youtube.com/watch?v=qcQ6x123KwU&start=15">'
                    . ' https://www.youtube.com/watch?v=qcQ6x123KwU</video>',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['start' => '15'],
            ],
            'video tag - youtu.be?t=15' => [
                'input' => '<video controls="true"><source src="https://youtu.be/qcQ6x123KwU?t=15">'
                    . ' https://youtu.be/qcQ6x123KwU</video>',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['start' => '15'],
            ],

            // End parameter.
            'plain text - youtu.be?end=15' => [
                'input' => 'https://youtu.be/qcQ6x123KwU?end=15',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['end' => '15'],
            ],
            'a tag - youtu.be?end=15' => [
                'input' => '<a href="https://youtu.be/qcQ6x123KwU?end=15">Testvideo</a>',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['end' => '15'],
            ],
            'iframe - youtube.com/watch?v=...&end=15' => [
                'input' => '<iframe width="560" height="315"'
                    . ' src="https://www.youtube.com/watch?v=qcQ6x123KwU&end=15"'
                    . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
                    . ' picture-in-picture" allowfullscreen></iframe>',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['end' => '15'],
            ],
            'plain text - youtube-nocookie.com/embed?end=15' => [
                'input' => 'https://www.youtube-nocookie.com/embed/qcQ6x123KwU?end=15',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['end' => '15'],
            ],
            'video tag - youtube.com/watch?v=...&end=15' => [
                'input' => '<video controls="true"><source src="https://www.youtube.com/watch?v=qcQ6x123KwU&end=15">'
                    . ' https://www.youtube.com/watch?v=qcQ6x123KwU</video>',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['end' => '15'],
            ],
            'video tag - youtu.be?end=15' => [
                'input' => '<video controls="true"><source src="https://youtu.be/qcQ6x123KwU?end=15">'
                    . ' https://youtu.be/qcQ6x123KwU</video>',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['end' => '15'],
            ],

            // Start + end parameters combined.
            'plain text - youtu.be?start=5&end=15' => [
                'input' => 'https://youtu.be/qcQ6x123KwU?start=5&end=15',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['start' => '5', 'end' => '15'],
            ],
            'iframe - youtube.com/watch?v=...&start=5&end=15' => [
                'input' => '<iframe width="560" height="315"'
                    . ' src="https://www.youtube.com/watch?v=qcQ6x123KwU&start=5&end=15"'
                    . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
                    . ' picture-in-picture" allowfullscreen></iframe>',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['start' => '5', 'end' => '15'],
            ],
            'video tag - youtube.com/watch?v=...&start=5&end=15' => [
                'input' => '<video controls="true"><source src="https://www.youtube.com/watch?v=qcQ6x123KwU&start=5&end=15">'
                    . ' https://www.youtube.com/watch?v=qcQ6x123KwU</video>',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['start' => '5', 'end' => '15'],
            ],
            'video tag - youtu.be?start=5&end=15' => [
                'input' => '<video controls="true"><source src="https://youtu.be/qcQ6x123KwU?start=5&end=15">'
                    . ' https://youtu.be/qcQ6x123KwU</video>',
                'videoid' => 'qcQ6x123KwU',
                'extraparams' => ['start' => '5', 'end' => '15'],
            ],
        ];
    }

    /**
     * Test that YouTube URLs with query parameters produce the two-click wrapper with correct params.
     *
     * @dataProvider youtube_url_with_params_provider
     * @covers ::filter
     * @param string $input The YouTube HTML snippet (will be wrapped in paragraphs).
     * @param string $videoid The expected video ID.
     * @param array $extraparams Extra expected params e.g. ['start' => '15'].
     */
    public function test_filter_preserves_query_params(string $input, string $videoid, array $extraparams): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $filter = $this->create_filter();
        $html = self::wrap_in_paragraphs($input);
        $filtered = $filter->filter($html);

        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringContainsString('id="yt___phpunit___' . $videoid . '"', $filtered);
        $this->assertStringContainsString(self::params_json_with($extraparams), $filtered);
        $this->assertStringContainsString('<p>YouTube eingefügt<br>', $filtered);
        $this->assertStringContainsString('<p>Das ist das Ende!</p>', $filtered);
    }

    /**
     * Data provider for plain YouTube URLs without any HTML wrapper.
     *
     * @return array
     */
    public static function plain_url_provider(): array {
        return [
            'plain watch URL in text' => [
                'input' => 'Check out this video: https://www.youtube.com/watch?v=PlainTextId and let me know!',
                'videoid' => 'PlainTextId',
                'textbefore' => 'Check out this video:',
                'textafter' => 'and let me know!',
            ],
            'plain embed URL in text' => [
                'input' => 'Embedded: https://www.youtube.com/embed/PlainTextId - enjoy!',
                'videoid' => 'PlainTextId',
                'textbefore' => 'Embedded:',
                'textafter' => '- enjoy!',
            ],
            'plain short URL in text' => [
                'input' => 'Short link: https://youtu.be/PlainTextId here!',
                'videoid' => 'PlainTextId',
                'textbefore' => 'Short link:',
                'textafter' => 'here!',
            ],
            'plain nocookie URL in text' => [
                'input' => 'Privacy-friendly: https://www.youtube-nocookie.com/watch?v=PlainTextId done.',
                'videoid' => 'PlainTextId',
                'textbefore' => 'Privacy-friendly:',
                'textafter' => 'done.',
            ],
        ];
    }

    /**
     * Test that plain YouTube URLs in text are replaced and surrounding text is preserved.
     *
     * @dataProvider plain_url_provider
     * @covers ::filter
     * @param string $input The full text input with a plain YouTube URL.
     * @param string $videoid The expected video ID.
     * @param string $textbefore Text expected before the wrapper.
     * @param string $textafter Text expected after the wrapper.
     */
    public function test_filter_plain_url_in_text(
        string $input,
        string $videoid,
        string $textbefore,
        string $textafter
    ): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $filter = $this->create_filter();
        $filtered = $filter->filter($input);

        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringContainsString('id="yt___phpunit___' . $videoid . '"', $filtered);
        $this->assertStringContainsString(self::default_params_json(), $filtered);
        $this->assertStringContainsString($textbefore, $filtered);
        $this->assertStringContainsString($textafter, $filtered);
    }

    /**
     * Test multiple YouTube videos combined in a single text block.
     *
     * @covers ::filter
     */
    public function test_filter_multiple_videos_combined(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $filter = $this->create_filter();

        $youtube = '<p>YouTube eingefügt<br>'
            . '<video controls="true"><source src="https://youtu.be/qcQ6x123KwUtz?start=3&end=13">'
            . ' https://youtu.be/qcQ6x123KwU</video>'
            . '</p>'
            . '<p>Das ist das Ende!</p>'
            . '<p>YouTube eingefügt 2<br>'
            . 'https://youtu.be/qcQ6x123KwU?start=5&end=15</p>'
            . '<p>Das ist das Ende 2!</p>';
        $filtered = $filter->filter($youtube);

        // First video.
        $this->assertStringContainsString('id="yt___phpunit___qcQ6x123KwUtz"', $filtered);
        $this->assertStringContainsString(self::params_json_with(['start' => '3', 'end' => '13']), $filtered);

        // Second video.
        $this->assertStringContainsString('id="yt___phpunit___qcQ6x123KwU"', $filtered);
        $this->assertStringContainsString(self::params_json_with(['start' => '5', 'end' => '15']), $filtered);

        // Surrounding text.
        $this->assertStringContainsString('<p>YouTube eingefügt<br>', $filtered);
        $this->assertStringContainsString('<p>Das ist das Ende!</p>', $filtered);
        $this->assertStringContainsString('<p>YouTube eingefügt 2<br>', $filtered);
        $this->assertStringContainsString('<p>Das ist das Ende 2!</p>', $filtered);
    }

    /**
     * Test multiple plain URLs in a single text line.
     *
     * @covers ::filter
     */
    public function test_filter_multiple_plain_urls_in_one_line(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $filter = $this->create_filter();

        $youtube = 'First video: https://www.youtube.com/watch?v=PlainTextId'
            . ' and second: https://youtu.be/SecondVidId?t=30 end.';
        $filtered = $filter->filter($youtube);

        $this->assertStringContainsString('id="yt___phpunit___PlainTextId"', $filtered);
        $this->assertStringContainsString('id="yt___phpunit___SecondVidId"', $filtered);
        $this->assertStringContainsString(self::params_json_with(['start' => '30']), $filtered);
        $this->assertStringContainsString('First video:', $filtered);
        $this->assertStringContainsString('and second:', $filtered);
        $this->assertStringContainsString('end.', $filtered);
    }

    /**
     * Test that an <a> tag with external-media-provider class is fully replaced.
     *
     * @covers ::filter
     */
    public function test_filter_external_media_provider_a_tag(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $filter = $this->create_filter();

        $youtube = '<p><a class="external-media-provider" href="https://youtu.be/K_xleuYgL_4?si=N_Euj21A_IG1EClu">'
            . ' https://youtu.be/K_xleuYgL_4?si=N_Euj21A_IG1EClu </a></p>';
        $filtered = $filter->filter($youtube);

        $this->assertStringContainsString('id="yt___phpunit___K_xleuYgL_4"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);

        // Original <a> tag must be completely replaced.
        $this->assertStringNotContainsString('<a class="external-media-provider"', $filtered);
        $this->assertStringNotContainsString('href="https://youtu.be/K_xleuYgL_4', $filtered);

        // Surrounding <p> tags preserved.
        $this->assertStringStartsWith('<p>', $filtered);
        $this->assertStringEndsWith('</p>', $filtered);
    }

    /**
     * Data provider for mediaplugin_videojs wrapped YouTube video tests.
     *
     * @return array
     */
    public static function mediaplugin_wrapped_provider(): array {
        return [
            'mediaplugin - youtu.be short URL' => [
                'href' => 'https://youtu.be/K_xleuYgL_4?si=N_Euj21A_IG1EClu',
                'linktext' => 'https://youtu.be/K_xleuYgL_4?si=N_Euj21A_IG1EClu',
                'videoid' => 'K_xleuYgL_4',
                'extraparams' => [],
                'notcontains' => ['youtube-nocookie'],
            ],
            'mediaplugin - youtube.com/watch' => [
                'href' => 'https://www.youtube.com/watch?v=TestVidId1',
                'linktext' => 'https://www.youtube.com/watch?v=TestVidId1',
                'videoid' => 'TestVidId1',
                'extraparams' => [],
                'notcontains' => [],
            ],
            'mediaplugin - youtube.com/embed' => [
                'href' => 'https://www.youtube.com/embed/TestVidId2',
                'linktext' => 'https://www.youtube.com/embed/TestVidId2',
                'videoid' => 'TestVidId2',
                'extraparams' => [],
                'notcontains' => [],
            ],
            'mediaplugin - youtube-nocookie.com/embed' => [
                'href' => 'https://www.youtube-nocookie.com/embed/TestVidId3',
                'linktext' => 'https://www.youtube-nocookie.com/embed/TestVidId3',
                'videoid' => 'TestVidId3',
                'extraparams' => [],
                'notcontains' => ['youtube-nocookie'],
            ],
            'mediaplugin - youtube-nocookie.com/watch' => [
                'href' => 'https://www.youtube-nocookie.com/watch?v=TestVidId4',
                'linktext' => 'https://www.youtube-nocookie.com/watch?v=TestVidId4',
                'videoid' => 'TestVidId4',
                'extraparams' => [],
                'notcontains' => ['youtube-nocookie'],
            ],
            'mediaplugin - youtu.be?t=30 (start param)' => [
                'href' => 'https://youtu.be/TestVidId5?t=30',
                'linktext' => 'https://youtu.be/TestVidId5?t=30',
                'videoid' => 'TestVidId5',
                'extraparams' => ['start' => '30'],
                'notcontains' => [],
            ],
            'mediaplugin - youtube.com/watch?v=...&start=45' => [
                'href' => 'https://www.youtube.com/watch?v=TestVidId6&start=45',
                'linktext' => 'https://www.youtube.com/watch?v=TestVidId6&start=45',
                'videoid' => 'TestVidId6',
                'extraparams' => ['start' => '45'],
                'notcontains' => [],
            ],
            'mediaplugin - youtube.com/embed?start=10&end=60' => [
                'href' => 'https://www.youtube.com/embed/TestVidId7?start=10&end=60',
                'linktext' => 'https://www.youtube.com/embed/TestVidId7?start=10&end=60',
                'videoid' => 'TestVidId7',
                'extraparams' => ['start' => '10', 'end' => '60'],
                'notcontains' => [],
            ],
            'mediaplugin - youtube-nocookie.com/embed?end=120' => [
                'href' => 'https://www.youtube-nocookie.com/embed/TestVidId8?end=120',
                'linktext' => 'https://www.youtube-nocookie.com/embed/TestVidId8?end=120',
                'videoid' => 'TestVidId8',
                'extraparams' => ['end' => '120'],
                'notcontains' => ['youtube-nocookie'],
            ],
        ];
    }

    /**
     * Test that mediaplugin_videojs wrapped YouTube videos are fully replaced by the two-click wrapper.
     *
     * This simulates the real filter chain: filter_mediaplugin runs first and wraps
     * the URL in a <div class="mediaplugin"><video>...</video></div>, then filter_mbsyoutube
     * must replace the entire wrapper.
     *
     * @dataProvider mediaplugin_wrapped_provider
     * @covers ::filter
     * @param string $href The URL used in the <a> href attribute.
     * @param string $linktext The visible link text.
     * @param string $videoid The expected video ID.
     * @param array $extraparams Extra expected params e.g. ['start' => '30'].
     * @param array $notcontains Strings that must NOT be in the output.
     */
    public function test_filter_mediaplugin_wrapped(
        string $href,
        string $linktext,
        string $videoid,
        array $extraparams,
        array $notcontains
    ): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $filter = $this->create_filter();
        $mediapluginfilter = new \filter_mediaplugin\text_filter($context, []);

        $originalhtml = '<p><a class="external-media-provider" href="' . $href . '">'
            . $linktext . '</a></p>';
        $mediapluginoutput = $mediapluginfilter->filter($originalhtml);
        $filtered = $filter->filter($mediapluginoutput);

        $this->assertStringContainsString('id="yt___phpunit___' . $videoid . '"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        if (!empty($extraparams)) {
            $this->assertStringContainsString(self::params_json_with($extraparams), $filtered);
        }

        // The mediaplugin wrapper must be fully replaced.
        $this->assertStringNotContainsString('class="mediaplugin mediaplugin_videojs', $filtered);
        $this->assertStringNotContainsString('data-setup-lazy', $filtered);

        foreach ($notcontains as $string) {
            $this->assertStringNotContainsString($string, $filtered);
        }

        // Surrounding <p> tags preserved.
        $this->assertStringStartsWith('<p>', $filtered);
        $this->assertStringEndsWith('</p>', $filtered);
    }

    /**
     * Data provider for TinyMCE Video tab tests (MBS-9201).
     *
     * When a user inserts a YouTube URL via the TinyMCE "Video" tab, the editor creates
     * a <video><source src="URL">URL</video> element. The filter must replace the entire
     * <video> element and must NOT double-replace the plain URL text content inside it.
     *
     * @return array
     */
    public static function tiny_video_tab_provider(): array {
        return [
            'tiny video tab - youtube.com/watch' => [
                'sourceurl' => 'https://www.youtube.com/watch?v=TinyTestVid1',
                'innertext' => ' https://www.youtube.com/watch?v=TinyTestVid1',
                'videoid' => 'TinyTestVid1',
                'extraparams' => [],
            ],
            'tiny video tab - youtu.be short URL' => [
                'sourceurl' => 'https://youtu.be/TinyTestVid2',
                'innertext' => ' https://youtu.be/TinyTestVid2',
                'videoid' => 'TinyTestVid2',
                'extraparams' => [],
            ],
            'tiny video tab - youtube.com/watch with start param' => [
                'sourceurl' => 'https://www.youtube.com/watch?v=TinyTestVid3&start=20',
                'innertext' => ' https://www.youtube.com/watch?v=TinyTestVid3',
                'videoid' => 'TinyTestVid3',
                'extraparams' => ['start' => '20'],
            ],
            'tiny video tab - youtube-nocookie.com/watch' => [
                'sourceurl' => 'https://www.youtube-nocookie.com/watch?v=TinyTestVid4',
                'innertext' => ' https://www.youtube-nocookie.com/watch?v=TinyTestVid4',
                'videoid' => 'TinyTestVid4',
                'extraparams' => [],
            ],
        ];
    }

    /**
     * Test that YouTube URLs inserted via the TinyMCE Video tab are handled correctly (MBS-9201).
     *
     * The filter must replace the entire <video> element with a single two-click wrapper
     * and must NOT produce a double replacement from the plain URL text inside the <video> body.
     *
     * @dataProvider tiny_video_tab_provider
     * @covers ::filter
     * @param string $sourceurl The URL in the <source src="..."> attribute.
     * @param string $innertext The plain text URL inside the <video> element body.
     * @param string $videoid The expected video ID.
     * @param array $extraparams Extra expected params.
     */
    public function test_filter_tiny_video_tab(
        string $sourceurl,
        string $innertext,
        string $videoid,
        array $extraparams
    ): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $filter = $this->create_filter();

        $html = '<p>YouTube eingefügt<br>'
            . '<video controls="true"><source src="' . $sourceurl . '">'
            . $innertext . '</video>'
            . '</p>'
            . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($html);

        // Two-click wrapper must be present with correct video ID.
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringContainsString('id="yt___phpunit___' . $videoid . '"', $filtered);

        if (!empty($extraparams)) {
            $this->assertStringContainsString(self::params_json_with($extraparams), $filtered);
        } else {
            $this->assertStringContainsString(self::default_params_json(), $filtered);
        }

        // Original <video> element must be completely removed.
        $this->assertStringNotContainsString('<video', $filtered);
        $this->assertStringNotContainsString('</video>', $filtered);
        $this->assertStringNotContainsString('<source', $filtered);

        // Exactly ONE two-click wrapper — no double replacement.
        $this->assertEquals(
            1,
            substr_count($filtered, 'mbsyoutube-twoclickwarning-wrapper'),
            'There should be exactly one two-click wrapper, not a double replacement.'
        );

        // Surrounding text preserved.
        $this->assertStringContainsString('<p>YouTube eingefügt<br>', $filtered);
        $this->assertStringContainsString('<p>Das ist das Ende!</p>', $filtered);
    }

    /**
     * Test that empty or non-string input is returned as-is.
     *
     * @covers ::filter
     */
    public function test_filter_returns_empty_input_unchanged(): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $filter = $this->create_filter();

        $this->assertSame('', $filter->filter(''));
        $this->assertSame('No YouTube here.', $filter->filter('No YouTube here.'));
    }
}
