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
 * Unit tests.
 *
 * @package filter_mbsyoutube
 * @category test
 * @copyright 2019 Franziska Hübler, 2019 Peter Mayer, ISB Bayern
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \filter_mbsyoutube\text_filter
 */
final class text_filter_test extends \advanced_testcase {
    /**
     * Test cases for filter_mbsyoutube.
     *
     * @covers ::filter
     */
    public function test_links(): void {
        global $DB, $CFG;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        \core\plugininfo\media::set_enabled_plugins(''); // Disable core mediaplugin.

        // Enable filter mbsyoutube.
        $filterobject = new \stdClass();
        $filterobject->filter = 'mbsyoutube';
        $filterobject->contextid = $context->id;
        $filterobject->active = 1;
        $filterobject->sortorder = 0;
        $DB->insert_record('filter_active', $filterobject);

        $filter = new text_filter($context, []);

        // Expected significant part for the next few assertions.
        $expected = '<div class="mbsyoutube-twoclickwarning-boxtext">'
        . '<strong>Data protection notice</strong>'
        . '<br />As soon as the video is played, personal <a href="https://policies.google.com/privacy" '
        . 'target="_blank" style="color:#e3e3e3 !important;">data</a> such as the IP address is transmitted to YouTube'
        . '</div>
        <input type="button" class="mbsyoutube-twoclickwarning-button mbsyoutube-confirm" value="'
        . 'Watch video anyway ✓'
        . '"/>';

        $expected2 = '{"modestbranding":1,"iv_load_policy":3,"enablejsapi":1,"origin":"' . $CFG->wwwroot .'"}';
        $expected3 = 'id="yt___phpunit___qcQ6x123KwU"';
        $expected4 = '<p>YouTube eingefügt<br>';
        $expected5 = '<p>Das ist das Ende!</p>';

        // A a Tag with youtube url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<a href="https://www.youtube.com/watch?v=qcQ6x123KwU">Link zum Video</a></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);

        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Youtube url as plain text.
        $youtube = '<p>YouTube eingefügt<br>'
        . 'https://www.youtube.com/watch?v=qcQ6x123KwU</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // A a Tag with youtube-nocookie url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<a href="https://www.youtube-nocookie.com/watch?v=qcQ6x123KwU">Link zum Video</a></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Youtube-nocookie url as plain text.
        $youtube = '<p>YouTube eingefügt<br>'
        . 'https://www.youtube-nocookie.com/watch?v=qcQ6x123KwU</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // A a Tag with youtube embed url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<a href="https://www.youtube.com/embed/qcQ6x123KwU">Link zum Video</a></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Youtube url as plain embed  text.
        $youtube = '<p>YouTube eingefügt<br>'
        . 'https://www.youtube.com/embed/qcQ6x123KwU</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // A a Tag with youtube-nocookie embed  url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<a href="https://www.youtube-nocookie.com/embed/qcQ6x123KwU">Link zum Video</a></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Youtube-nocookie url as plain embed  text.
        $youtube = '<p>YouTube eingefügt<br>'
        . 'https://www.youtube-nocookie.com/embed/qcQ6x123KwU</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Iframe with youtube embed url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<iframe width="560" height="315" src="https://www.youtube.com/embed/qcQ6x123KwU"'
        . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
        . ' picture-in-picture" allowfullscreen></iframe></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Iframe with youtube-nocookie embed url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/qcQ6x123KwU"'
        . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
        . ' picture-in-picture" allowfullscreen></iframe></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Iframe with youtube watch url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<iframe width="560" height="315" src="https://www.youtube.com/watch?v=qcQ6x123KwU"'
        . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
        . ' picture-in-picture" allowfullscreen></iframe></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Iframe with youtube-nocookie watch url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/watch?v=qcQ6x123KwU"'
        . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
        . ' picture-in-picture" allowfullscreen></iframe></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // A a Tag with youtube short url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<a href="https://youtu.be/qcQ6x123KwU">Link zum Video</a></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Youtube short url as plain text.
        $youtube = '<p>YouTube eingefügt<br>'
        . 'https://youtu.be/qcQ6x123KwU</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Video-Tag with youtube watch url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<video controls="true"><source src="https://www.youtube.com/watch?v=qcQ6x123KwU">'
        . ' https://www.youtube.com/watch?v=qcQ6x123KwU</video>'
        . '</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Video-Tag with youtube short url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<video controls="true"><source src="https://youtu.be/qcQ6x123KwU">'
        . ' https://youtu.be/qcQ6x123KwU</video>'
        . '</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Youtube short url with start parameter.
        $expected2 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","start":"15"}';
        $youtube = '<p>YouTube eingefügt<br>'
        . 'https://youtu.be/qcQ6x123KwU?t=15</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Youtube short url with start parameter.
        $youtube = '<p>YouTube eingefügt<br>'
        . 'https://youtu.be/qcQ6x123KwU?start=15</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Iframe with youtube watch url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<iframe width="560" height="315" src="https://www.youtube.com/watch?v=qcQ6x123KwU&start=15"'
        . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
        . ' picture-in-picture" allowfullscreen></iframe></p>'
        . '<p>Das ist das Ende!</p>';

        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Iframe with youtube watch url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/watch?v=qcQ6x123KwU&start=15"'
        . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
        . ' picture-in-picture" allowfullscreen></iframe></p>'
        . '<p>Das ist das Ende!</p>';

        $expected2 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","start":"15"}';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Video-Tag with youtube watch url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<video controls="true"><source src="https://www.youtube.com/watch?v=qcQ6x123KwU&start=15">'
        . ' https://www.youtube.com/watch?v=qcQ6x123KwU</video>'
        . '</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Video-Tag with youtube short url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<video controls="true"><source src="https://youtu.be/qcQ6x123KwU?t=15">'
        . ' https://youtu.be/qcQ6x123KwU</video>'
        . '</p>'
        . '<p>Das ist das Ende!</p>';
        $expected2 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","start":"15"}';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Youtube short url with end parameter.
        $youtube = '<p>YouTube eingefügt<br>'
        . 'https://youtu.be/qcQ6x123KwU?end=15</p>'
        . '<p>Das ist das Ende!</p>';
        $expected2 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","end":"15"}';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Youtube short url with end parameter.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<a href="https://youtu.be/qcQ6x123KwU?end=15">Testvideo</a></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Iframe with youtube watch url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<iframe width="560" height="315" src="https://www.youtube.com/watch?v=qcQ6x123KwU&end=15"'
        . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
        . ' picture-in-picture" allowfullscreen></iframe></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Youtube-nocookie url as plain embed  text.
        $youtube = '<p>YouTube eingefügt<br>'
        . 'https://www.youtube-nocookie.com/embed/qcQ6x123KwU?end=15</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Video-Tag with youtube watch url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<video controls="true"><source src="https://www.youtube.com/watch?v=qcQ6x123KwU&end=15">'
        . ' https://www.youtube.com/watch?v=qcQ6x123KwU</video>'
        . '</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Video-Tag with youtube short url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<video controls="true"><source src="https://youtu.be/qcQ6x123KwU?end=15">'
        . ' https://youtu.be/qcQ6x123KwU</video>'
        . '</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Youtube short url with start and end parameter.
        $youtube = '<p>YouTube eingefügt<br>'
        . 'https://youtu.be/qcQ6x123KwU?start=5&end=15</p>'
        . '<p>Das ist das Ende!</p>';
        $expected2 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","start":"5","end":"15"}';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Iframe with youtube watch url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<iframe width="560" height="315" src="https://www.youtube.com/watch?v=qcQ6x123KwU&start=5&end=15"'
        . ' frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope;'
        . ' picture-in-picture" allowfullscreen></iframe></p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Video-Tag with youtube watch url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<video controls="true"><source src="https://www.youtube.com/watch?v=qcQ6x123KwU&start=5&end=15">'
        . ' https://www.youtube.com/watch?v=qcQ6x123KwU</video>'
        . '</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Video-Tag with youtube short url.
        $youtube = '<p>YouTube eingefügt<br>'
        . '<video controls="true"><source src="https://youtu.be/qcQ6x123KwU?start=5&end=15">'
        . ' https://youtu.be/qcQ6x123KwU</video>'
        . '</p>'
        . '<p>Das ist das Ende!</p>';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);

        // Testcase: multiple YouTube Videos combined.
        $expected = 'id="yt___phpunit___qcQ6x123KwU"';
        $expected2 = 'id="yt___phpunit___qcQ6x123KwUtz"';
        $youtube = '<p>YouTube eingefügt<br>'
        . '<video controls="true"><source src="https://youtu.be/qcQ6x123KwUtz?start=3&end=13">'
        . ' https://youtu.be/qcQ6x123KwU</video>'
        . '</p>'
        . '<p>Das ist das Ende!</p>'
        . '<p>YouTube eingefügt 2<br>'
        . 'https://youtu.be/qcQ6x123KwU?start=5&end=15</p>'
        . '<p>Das ist das Ende 2!</p>';
        $expected3 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","start":"3","end":"13"}';
        $expected6 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","start":"5","end":"15"}';
        $expected7 = '<p>YouTube eingefügt 2<br>';
        $expected8 = '<p>Das ist das Ende 2!</p>';

        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);
        $this->assertStringContainsString($expected6, $filtered);
        $this->assertStringContainsString($expected7, $filtered);
        $this->assertStringContainsString($expected8, $filtered);

        // Testcase: Plain YouTube URLs without any HTML wrapper (no <a>, <iframe>, <video> tags).
        // These are raw URLs directly in text, not embedded in any HTML element.
        $expected = '<div class="mbsyoutube-twoclickwarning-boxtext">'
        . '<strong>Data protection notice</strong>';
        $expected2 = '{"modestbranding":1,"iv_load_policy":3,"enablejsapi":1,"origin":"' . $CFG->wwwroot .'"}';
        $expected3 = 'id="yt___phpunit___PlainTextId"';

        // Plain watch URL without HTML wrapper.
        $youtube = 'Check out this video: https://www.youtube.com/watch?v=PlainTextId and let me know!';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString('Check out this video:', $filtered);
        $this->assertStringContainsString('and let me know!', $filtered);

        // Plain embed URL without HTML wrapper.
        $youtube = 'Embedded: https://www.youtube.com/embed/PlainTextId - enjoy!';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString('Embedded:', $filtered);
        $this->assertStringContainsString('- enjoy!', $filtered);

        // Plain short URL without HTML wrapper.
        $youtube = 'Short link: https://youtu.be/PlainTextId here!';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString('Short link:', $filtered);
        $this->assertStringContainsString('here!', $filtered);

        // Plain nocookie URL without HTML wrapper.
        $youtube = 'Privacy-friendly: https://www.youtube-nocookie.com/watch?v=PlainTextId done.';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString('Privacy-friendly:', $filtered);
        $this->assertStringContainsString('done.', $filtered);

        // Multiple plain URLs in one text without HTML.
        $expected4 = 'id="yt___phpunit___SecondVidId"';
        $expected5 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","start":"30"}';
        $youtube = 'First video: https://www.youtube.com/watch?v=PlainTextId and second: https://youtu.be/SecondVidId?t=30 end.';
        $filtered = $filter->filter($youtube);
        $this->assertStringContainsString($expected, $filtered);
        $this->assertStringContainsString($expected3, $filtered);
        $this->assertStringContainsString($expected4, $filtered);
        $this->assertStringContainsString($expected5, $filtered);
        $this->assertStringContainsString('First video:', $filtered);
        $this->assertStringContainsString('and second:', $filtered);
        $this->assertStringContainsString('end.', $filtered);

        // Testcase: YouTube short URL in an <a> tag with external-media-provider class.
        // The URL appears both in href attribute and as visible link text.
        // This tests that the <a> tag is replaced with the two-click wrapper.
        $youtube = '<p><a class="external-media-provider" href="https://youtu.be/K_xleuYgL_4?si=N_Euj21A_IG1EClu">'
            . ' https://youtu.be/K_xleuYgL_4?si=N_Euj21A_IG1EClu </a></p>';
        $filtered = $filter->filter($youtube);

        // Verify the two-click wrapper was generated.
        $this->assertStringContainsString('id="yt___phpunit___K_xleuYgL_4"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);

        // Verify the original <a> tag was completely replaced (not present anymore).
        $this->assertStringNotContainsString('<a class="external-media-provider"', $filtered);
        $this->assertStringNotContainsString('href="https://youtu.be/K_xleuYgL_4', $filtered);

        // Verify surrounding <p> tags are preserved.
        $this->assertStringStartsWith('<p>', $filtered);
        $this->assertStringEndsWith('</p>', $filtered);

        // Testcase: mediaplugin_videojs wrapped YouTube video.
        $originalhtml = '<p><a class="external-media-provider" href="https://youtu.be/K_xleuYgL_4?si=N_Euj21A_IG1EClu">'
            . 'https://youtu.be/K_xleuYgL_4?si=N_Euj21A_IG1EClu</a></p>';
        $mediapluginfilter = new \filter_mediaplugin\text_filter($context, []);
        $youtube = $mediapluginfilter->filter($originalhtml);
        // The entire div wrapper with nested video element should be replaced.
        $filtered = $filter->filter($youtube);

        // Verify the two-click wrapper was generated with correct video ID.
        $this->assertStringContainsString('id="yt___phpunit___K_xleuYgL_4"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);

        // Verify the original mediaplugin div wrapper was completely replaced (not present anymore).
        $this->assertStringNotContainsString('class="mediaplugin mediaplugin_videojs', $filtered);
        $this->assertStringNotContainsString('data-setup-lazy', $filtered);
        $this->assertStringNotContainsString('class="video-js"', $filtered);

        // Verify surrounding <p> tags are preserved.
        $this->assertStringStartsWith('<p>', $filtered);
        $this->assertStringEndsWith('</p>', $filtered);

        // Testcase: mediaplugin_videojs with youtube.com/watch URL (standard format).
        $originalhtml = '<p><a class="external-media-provider" href="https://www.youtube.com/watch?v=TestVidId1">'
            . 'https://www.youtube.com/watch?v=TestVidId1</a></p>';
        $youtube = $mediapluginfilter->filter($originalhtml);
        $filtered = $filter->filter($youtube);

        $this->assertStringContainsString('id="yt___phpunit___TestVidId1"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringNotContainsString('class="mediaplugin mediaplugin_videojs', $filtered);
        $this->assertStringNotContainsString('data-setup-lazy', $filtered);

        // Testcase: mediaplugin_videojs with youtube.com/embed URL.
        $originalhtml = '<p><a class="external-media-provider" href="https://www.youtube.com/embed/TestVidId2">'
            . 'https://www.youtube.com/embed/TestVidId2</a></p>';
        $youtube = $mediapluginfilter->filter($originalhtml);
        $filtered = $filter->filter($youtube);

        $this->assertStringContainsString('id="yt___phpunit___TestVidId2"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringNotContainsString('class="mediaplugin mediaplugin_videojs', $filtered);

        // Testcase: mediaplugin_videojs with youtube-nocookie.com URL.
        $originalhtml = '<p><a class="external-media-provider" href="https://www.youtube-nocookie.com/embed/TestVidId3">'
            . 'https://www.youtube-nocookie.com/embed/TestVidId3</a></p>';
        $youtube = $mediapluginfilter->filter($originalhtml);
        $filtered = $filter->filter($youtube);

        $this->assertStringContainsString('id="yt___phpunit___TestVidId3"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringNotContainsString('youtube-nocookie', $filtered);

        // Testcase: mediaplugin_videojs with youtube-nocookie.com/watch URL.
        $originalhtml = '<p><a class="external-media-provider" href="https://www.youtube-nocookie.com/watch?v=TestVidId4">'
            . 'https://www.youtube-nocookie.com/watch?v=TestVidId4</a></p>';
        $youtube = $mediapluginfilter->filter($originalhtml);
        $filtered = $filter->filter($youtube);

        $this->assertStringContainsString('id="yt___phpunit___TestVidId4"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringNotContainsString('class="mediaplugin mediaplugin_videojs', $filtered);
        $this->assertStringNotContainsString('youtube-nocookie', $filtered);

        // Testcase: mediaplugin_videojs with youtu.be URL and start parameter (t=30).
        $originalhtml = '<p><a class="external-media-provider" href="https://youtu.be/TestVidId5?t=30">'
            . 'https://youtu.be/TestVidId5?t=30</a></p>';
        $youtube = $mediapluginfilter->filter($originalhtml);
        $expected2 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","start":"30"}';
        $filtered = $filter->filter($youtube);

        $this->assertStringContainsString('id="yt___phpunit___TestVidId5"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringNotContainsString('class="mediaplugin mediaplugin_videojs', $filtered);

        // Testcase: mediaplugin_videojs with youtube.com/watch URL and start parameter.
        $originalhtml = '<p><a class="external-media-provider" href="https://www.youtube.com/watch?v=TestVidId6&start=45">'
            . 'https://www.youtube.com/watch?v=TestVidId6&start=45</a></p>';
        $youtube = $mediapluginfilter->filter($originalhtml);
        $expected2 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","start":"45"}';
        $filtered = $filter->filter($youtube);

        $this->assertStringContainsString('id="yt___phpunit___TestVidId6"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringNotContainsString('class="mediaplugin mediaplugin_videojs', $filtered);

        // Testcase: mediaplugin_videojs with youtube.com/embed URL and start+end parameters.
        $originalhtml = '<p><a class="external-media-provider" href="https://www.youtube.com/embed/TestVidId7?start=10&end=60">'
            . 'https://www.youtube.com/embed/TestVidId7?start=10&end=60</a></p>';
        $youtube = $mediapluginfilter->filter($originalhtml);
        $expected2 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","start":"10","end":"60"}';
        $filtered = $filter->filter($youtube);

        $this->assertStringContainsString('id="yt___phpunit___TestVidId7"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringNotContainsString('class="mediaplugin mediaplugin_videojs', $filtered);

        // Testcase: mediaplugin_videojs with youtube-nocookie.com/embed URL and end parameter.
        $originalhtml = '<p><a class="external-media-provider" href="https://www.youtube-nocookie.com/embed/TestVidId8?end=120">'
            . 'https://www.youtube-nocookie.com/embed/TestVidId8?end=120</a></p>';
        $youtube = $mediapluginfilter->filter($originalhtml);
        $expected2 = '{"modestbranding":1,"iv_load_policy":3,'
        . '"enablejsapi":1,"origin":"' . $CFG->wwwroot . '","end":"120"}';
        $filtered = $filter->filter($youtube);

        $this->assertStringContainsString('id="yt___phpunit___TestVidId8"', $filtered);
        $this->assertStringContainsString('mbsyoutube-twoclickwarning-boxtext', $filtered);
        $this->assertStringContainsString($expected2, $filtered);
        $this->assertStringNotContainsString('class="mediaplugin mediaplugin_videojs', $filtered);
        $this->assertStringNotContainsString('youtube-nocookie', $filtered);
    }
}
