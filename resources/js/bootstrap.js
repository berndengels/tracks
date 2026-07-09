import moment from 'moment';
import toastr from 'toastr';
moment.locale('de');
import axios from "axios";
import { createApp } from "vue"

window.createApp = createApp;
window.axios = axios;
window.moment = moment;
window.toastr = toastr;
window.L = require('leaflet');

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
//    axios.defaults.baseURL = process.env.MIX_API_URL;
axios.defaults.withCredentials = true;

try {
    //	require('bootstrap');
//    require('bootstrap/js/dist/carousel');
//    require('bootstrap/js/dist/offcanvas');
// require('bootstrap/js/dist/alert');
    require('bootstrap/js/dist/button');
    require('bootstrap/js/dist/collapse');
    require('bootstrap/js/dist/dropdown');
// require('bootstrap/js/dist/modal');
// require('bootstrap/js/dist/popover');
// require('bootstrap/js/dist/scrollspy');
// require('bootstrap/js/dist/tab');
// require('bootstrap/js/dist/toast');
    require('bootstrap/js/dist/tooltip');
} catch (e) {
    console.error(e)
}
