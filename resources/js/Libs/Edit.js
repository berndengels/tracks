import $ from "jquery";
class Edit {
	toggle(route, attribute, reload = false, switchSelector = 'i.switch') {
		const token = $('[name="csrf-token"]').attr('content'),
			clOn = "fs-3 fas green fa-toggle-on on",
			clOff = "fs-3 fas grey fa-toggle-off off";

		$('.' + attribute).click(e => {
			var $t = $(e.target),
				toggle = $t.hasClass('on') ? 0 : 1,
				id = $t.parent('td').attr('rel'),
				url = route.replace(/\/$/,"") + "/" + id,
				data = {
					"id": id,
					"attribute": attribute,
					"value": toggle,
					"_token": token
				};
			axios.post(url, data)
				.then(resp => {
					console.info('resp.data', resp.data)
					if(resp.data[attribute]) {
						$t.removeClass(clOff).addClass(clOn);
//						$("#"+resp.data.name).show();
					} else {
						$t.removeClass(clOn).addClass(clOff);
//						$("#"+resp.data.name).hide();
					}
					if(reload) {
						location.reload()
					}
				}).catch(err => console.error(err))
		});
	}
}
export default Edit
