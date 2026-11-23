
class Autocomplete {
	init(apiUrl, selector = '#autocomplete', selectorList = '#listAutocomplete', routePrefix = null, formName = 'frmSearch', routeSufix = null) {
        $(selector).keyup(e => {
            const $autocomplete = $(e.target), $v = $autocomplete.val();
            if($v.length > 1) {
                let frm = new FormData();
                frm.append('search', $v);
                axios.post(apiUrl, frm)
                    .then(resp => {
                        const $listAutocomplete = $(selectorList);
                        if(resp.data.success && resp.data.items) {
                            $listAutocomplete.removeClass('d-none').empty();

                            $.each(resp.data.items, (id, name) => {
                                let $li = $('<li>');
                                $li.attr('data-value', id).text(name).appendTo($listAutocomplete);
                            });
                            $listAutocomplete.click(e => {
                                let $t = $(e.target);
                                $autocomplete.val($t.text());
                                let $inpId = $autocomplete.next('input[name="id"]');
                                if($inpId) {
                                    let id = $t.data('value').toString();
                                    $inpId.val($t.data('value'));
                                    if(routePrefix) {
//                                        location.href = routePrefix + '/' + id +  "/edit";
//                                        location.href = routePrefix + '/search';
                                        if(formName) {
                                            document[formName].submit()
                                        }
                                    } else {
                                        location.href = id +  "/edit";
                                    }
                                }
                                $listAutocomplete.addClass('d-none').empty();
                            })
                        } else {
                            $listAutocomplete.addClass('d-none').empty();
                        }
                    })
                    .catch(err => console.error(err))
            }
        });
	}
}
export default Autocomplete
