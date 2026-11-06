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
 * Stores the consent to load YouTube videos in the browser for the current session.
 *
 * @module     filter_mbsyoutube/sethasuseraccepted
 * @copyright  2019 Peter Mayer, ISB Bayern, peter.mayer@isb.bayern.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {init as initYTPlayers} from 'filter_mbsyoutube/youtube_api';

const STORAGEKEY = 'filter_mbsyoutube_consent';
const LIFETIME = 24 * 60 * 60 * 1000;

let sessionhash = '';
let accepted = false;
let apiloaded = false;
let initialised = false;

/**
 * Initialise the two click boxes.
 *
 * @param {string} hash Hash of the current session
 */
export const init = (hash) => {
    if (initialised) {
        return;
    }
    initialised = true;
    sessionhash = hash;
    accepted = hasConsent();
    initWrappers();
    if (accepted) {
        loadYouTubeApi();
    }
    // Wrappers might be added to the document later: Observe the DOM for a change.
    new MutationObserver(initWrappers).observe(document, {
        subtree: true,
        childList: true
    });
};

/**
 * Check if the user has given the consent in the current session within the lifetime.
 *
 * @returns {boolean}
 */
function hasConsent() {
    let consent = null;
    try {
        consent = JSON.parse(window.localStorage.getItem(STORAGEKEY));
    } catch (e) {
        return false;
    }
    if (consent && consent.session === sessionhash && Date.now() - consent.ts < LIFETIME) {
        return true;
    }
    try {
        window.localStorage.removeItem(STORAGEKEY);
    } catch (e) {
        // Nothing to clean up if the storage is not available.
    }
    return false;
}

/**
 * Store the consent for the current session.
 */
function storeConsent() {
    try {
        window.localStorage.setItem(STORAGEKEY, JSON.stringify({session: sessionhash, ts: Date.now()}));
    } catch (e) {
        // Without storage the consent is only valid for the current page.
    }
}

/**
 * Load the YouTube iframe API once and initialise the players.
 */
function loadYouTubeApi() {
    if (apiloaded) {
        return;
    }
    apiloaded = true;
    const ytscript = document.createElement('script');
    ytscript.onload = () => initYTPlayers();
    ytscript.src = 'https://www.youtube.com/iframe_api';
    document.head.appendChild(ytscript);
}

/**
 * Handle the click on the confirm button.
 *
 * @param {Event} e
 */
function onConfirm(e) {
    e.stopPropagation();
    storeConsent();
    accepted = true;
    initWrappers();
    loadYouTubeApi();
}

/**
 * Bind the confirm buttons and show the wrappers, which are hidden until the consent is checked.
 */
function initWrappers() {
    document.querySelectorAll('.mbsyoutube-twoclickwarning-wrapper:not(.mbsyoutube-ready)').forEach((wrapper) => {
        wrapper.querySelector('.mbsyoutube-confirm')?.addEventListener('click', onConfirm);
        wrapper.classList.add('mbsyoutube-ready');
    });
    if (accepted) {
        document.querySelectorAll('.mbsyoutube-confirm').forEach((button) => {
            button.closest('.mbsyoutube-twoclickwarning-buttonbox').hidden = true;
        });
    }
}
