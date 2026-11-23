
class SearchFilter {
	init(route, attribute, reload = false, switchSelector = 'i.switch') {
		const token = $('[name="csrf-token"]').attr('content'),
			clOn = "fs-3 fas green fa-toggle-on on",
			clOff = "fs-3 fas grey fa-toggle-off off";

		$(switchSelector).click(e => {
			var $t = $(e.target),
				toggle = $t.hasClass('on') ? 0 : 1,
				id = $t.parent('td').attr('rel'),
				url = route.replace(/\/$/,"") + "/" + id,
				data = {
					"attribute": attribute,
					"value": toggle,
					"_token": token
				}
			;
			$.post(url, data).done(resp => {
				if(resp[attribute]) {
					$t.removeClass(clOff).addClass(clOn);
					$("#"+resp.name).show();
				} else {
					$t.removeClass(clOn).addClass(clOff);
					$("#"+resp.name).hide();
				}
				if(reload) {
					location.reload()
				}
			});
		});
	}
}
export default SearchFilter
