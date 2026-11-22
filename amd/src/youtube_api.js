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
 * Javascript controller YouTube Videos.
 *
 * @module     filter_mbsyoutube/youtube_api
 * @copyright  2019 Peter Mayer, ISB Bayern, peter.mayer@isb.bayern.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** @var {WeakSet} elements which already have a player */
const players = new WeakSet();
let copies = 0;

/**
 * Initialise the players as soon as the API is ready.
 *
 * @param {function} onReady called when the API is ready and the players are initialised
 */
export const init = (onReady) => {
    whenApiReady(() => {
        initPlayer();
        onReady();
        // Videos might be added to the document later: Observe the DOM for a change.
        new MutationObserver(initPlayer).observe(document, {
            subtree: true,
            childList: true
        });
    });
};

/**
 * Run the callback as soon as YT.Player is available.
 *
 * The iframe API loads the widget API asynchronously, so YT.Player might not exist yet when the iframe API is loaded.
 * YT.ready() is not officially documented, therefore fall back to the documented onYouTubeIframeAPIReady.
 *
 * @param {function} callback
 */
function whenApiReady(callback) {
    if (!window.YT) {
        return;
    }
    if (typeof window.YT.ready === 'function') {
        window.YT.ready(callback);
    } else if (window.YT.Player) {
        callback();
    } else {
        // Do not overwrite an existing handler, call it before our callback.
        const previous = window.onYouTubeIframeAPIReady;
        window.onYouTubeIframeAPIReady = () => {
            if (typeof previous === 'function') {
                previous();
            }
            callback();
        };
    }
}

/**
 * Create a player for each player element which has none yet.
 *
 * Copies of an element (e.g. in a modal of format_tiles) have the same id as the original, so they get a new unique id.
 * The YouTube API replaces the div by an iframe, therefore only divs are taken into account.
 */
function initPlayer() {
    document.querySelectorAll('div.mbsyoutube-ytiframe').forEach((node) => {
        if (players.has(node)) {
            return;
        }
        players.add(node);
        const videoid = node.id.split('___')[2];
        if (document.getElementById(node.id) !== node) {
            copies++;
            node.id += '___copy' + copies;
        }
        node.removeAttribute('hidden');
        // eslint-disable-next-line no-undef
        new YT.Player(node, {
            videoId: videoid,
            playerVars: JSON.parse(node.getAttribute('data-extern')),
            events: {
                'onError': catchError
            }
        });
    });
}

/**
 * Catch errors.
 * @param {object} event
 */
function catchError(event) {
    if (event.data == 100) {
        window.console.log("Error - The video is not accessible!");
    }
}
