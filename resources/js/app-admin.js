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
//    require('bootstrap/js/dist/tab');
// require('bootstrap/js/dist/toast');
    require('bootstrap/js/dist/tooltip');
} catch (e) {
    console.error(e)
}
import axios from "axios";
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.baseURL = process.env.MIX_API_URL;
axios.defaults.withCredentials = true;
window.axios = axios;

window.$ = window.jQuery = jQuery = $ = require('jquery');

import Edit from "L@/Edit";
window.Edit = global.Edit = new Edit;

$(document).ready(() => {
    $(".delSoft,.delsoft").click(()=> {
        return confirm("Daten wirklich löschen");
    });
});
