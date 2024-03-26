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
 * Unit tests.
 *
 * @package filter_mbsyoutube
 * @category test
 * @copyright 2019 Franziska Hübler, 2019 Peter Mayer, ISB Bayern
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_mbsyoutube;

use advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/filter/mbsyoutube/filter.php'); // Include the code to test.

/**
 * Test case for filter_mbsyoutube.
 *
 * @copyright 2019 Peter Mayer, ISB Bayern
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_test extends advanced_testcase {
    /**
     * Test case for filter_mbsyoutube.
     *
     * @covers \filter_mbsyoutube\filter::filter
     */
    public function test_links() {
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

        $filter = new \filter_mbsyoutube($context, []);

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
    }
}
