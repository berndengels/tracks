import './bootstrap';

import trackStore from "v@/store/trackStore";
import Tracks from "c@/Tracks.vue";

window.$ = window.jQuery = jQuery = $ = require('jquery');

$(document).ready(() => {
    const tracks = document.getElementById('tracks');
    switch (true) {
        case $(tracks).is(":visible"):
            createApp(Tracks, {...tracks.dataset}).use(trackStore).mount(tracks);
            break;
    }
});
