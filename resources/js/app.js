import toastr from 'toastr';
import axios from "axios";
import { createApp } from "vue";
import { createPinia } from 'pinia';

window.createApp = createApp;
window.toastr = toastr;

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.baseURL = process.env.MIX_API_URL;
axios.defaults.withCredentials = true;
window.axios = axios;

try {
    //	require('bootstrap');
//    require('bootstrap/js/dist/carousel');
    require('bootstrap/js/dist/offcanvas');
// require('bootstrap/js/dist/alert');
    require('bootstrap/js/dist/button');
    require('bootstrap/js/dist/collapse');
    require('bootstrap/js/dist/dropdown');
// require('bootstrap/js/dist/modal');
// require('bootstrap/js/dist/popover');
// require('bootstrap/js/dist/scrollspy');
// require('bootstrap/js/dist/tab');
    require('bootstrap/js/dist/toast');
    require('bootstrap/js/dist/tooltip');
} catch (e) {
    console.error(e)
}

window.$ = window.jQuery = jQuery = $ = require('jquery');

const toastrOptions = {
    escapeHtml: false,
    closeButton: true,
    preventDuplicates: true,
    showDuration: 300,
    hideDuration: 1000,
    timeOut: 3000,
    extendedTimeOut: 3000,
//	timeOut: 0,
//	extendedTimeOut: 0,
    positionClass: "toast-top-center",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
};
window.toastrOptions = toastrOptions;

const myMessage = (msg, type = 'success', options= toastrOptions) => {
    window.toastr.options = $.extend({}, toastrOptions, options);
    switch (type) {
        case 'success':
            window.toastr.success(msg);
            break;
        case 'warning':
            window.toastr.warning(msg);
            break;
        case 'error':
            window.toastr.error(msg);
            break;
        case 'info':
        default:
            window.toastr.info(msg);
            break;
    }
}
window.myMessage = myMessage;

if($('[name="csrf-token"]').is(":visible")) {
    const csrfToken = $('[name="csrf-token"]').attr('content');
    $.ajaxSetup({
        headers: {'X-CSRF-Token': csrfToken},
    });
    window.csrfToken = csrfToken;
}
$(document).ajaxError(function( event, xhr, settings, thrownError ) {
    console.error('xhr', xhr);
});

// init Vue Components
import SailingTracksMap from "c@/SailingTracksMap.vue";
const app = createApp({});
app.component('sailing-tracks-map', SailingTracksMap).mount('#app');

$(document).ready(() => {
    $(".delSoft,.delsoft").click(()=> {
        return confirm("Daten wirklich löschen");
    });
/*
    const tracks = document.getElementById('tracks');
    switch (true) {
        case $(tracks).is(":visible"):
            app.component('SailingTracksMap', SailingTracksMap).mount('#app');
//            createApp(SailingTracksMap, {...tracks.dataset}).mount(tracks);
            break;
    }
*/
});
