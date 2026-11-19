import toastr from 'toastr';

window.$ = window.jQuery = jQuery = $ = require('jquery');

$(document).ready(() => {
    $(".delSoft,.delsoft").click(()=> {
        return confirm("Daten wirklich löschen");
    });
});
