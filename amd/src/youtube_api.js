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

let videos = {};
let players = {};

export const init = () => {
    whenApiReady(() => {
        initPlayer();
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
 * Initialize the Players.
 */
function initPlayer() {

    document.querySelectorAll('.mbsyoutube-ytiframe').forEach(function(node) {
        var playerid = node.id.split("___");
        var videoid = playerid[2];
        var videouniqid = playerid[1];
        getJsonObjectFromIdAttribut(videoid, 'data-extern', videouniqid);
    });

    loadPlayers();
}

/**
 * Initialise the players.
 */
function loadPlayers() {
    for (const [key, value] of Object.entries(videos)) {
        if (players[key]) {
            continue;
        }
        createYouTubePlayer(value.videoid, value.ytparam, key);
    }
}

/**
 * Create a YouTube player for a given video and add it to the players object.
 * @param {string} videoid
 * @param {*} ytparam
 * @param {string} uniqeid
 */
function createYouTubePlayer(videoid, ytparam, uniqeid) {
    const targetElement = document.getElementById('yt___' + uniqeid + '___' + videoid);
    if (targetElement) {
        targetElement.removeAttribute('hidden');
    }
    // eslint-disable-next-line no-undef
    players[uniqeid] = new YT.Player('yt___' + uniqeid + '___' + videoid, {
        videoId: videoid,
        playerVars: ytparam,
        events: {
            'onError': catchError
        }
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

/**
 * Make a json object from a data attribute and add it to the video object.
 * @param {string} videoid
 * @param {string} attribut
 * @param {string} uniqid
 */
function getJsonObjectFromIdAttribut(videoid, attribut, uniqid) {
    var jsonobj = JSON.parse(
        document.getElementById('yt___' + uniqid + '___' + videoid).getAttribute(attribut));
    videos[uniqid] = {ytparam: jsonobj, videoid: videoid};
}