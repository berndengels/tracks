
class Tooltip {
	prepare(triggerSelector, tooltipSelector = "#tooltip") {
		$(triggerSelector).click((e) => {
			const $tt = $(tooltipSelector),
				$target = $(e.target),
				offset = $target.offset(),
				info = $target.data('info')
			;

			if($tt.is(":visible")) {
				$tt.hide()
			}
			$tt.css({top: offset.top - 30, left: offset.left - 10, zIndex: 1000})
				.show()
				.find('.txt').html(info)
				.end()
				.click(e => $tt.hide())
			;
			setInterval(() => $tt.fadeOut(), 3000)
		});
	}
}
export default Tooltip
