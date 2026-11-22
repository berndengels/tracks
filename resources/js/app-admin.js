import toastr from 'toastr';

window.$ = window.jQuery = jQuery = $ = require('jquery');

require('bootstrap');
/*
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
    require('bootstrap/js/dist/tab');
// require('bootstrap/js/dist/toast');
    require('bootstrap/js/dist/tooltip');
} catch (e) {
    console.error(e)
}
*/
$(document).ready(() => {
    $(".delSoft,.delsoft").click(()=> {
        return confirm("Daten wirklich löschen");
    });
});
