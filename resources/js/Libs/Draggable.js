class Draggable {

	constructor(selector = ".draggable", trigger = ".trigger") {
		this.selector = selector;
		this.trigger = trigger;
		this.dragElement();
	}

	dragElement() {
		const cls = "drag";
		$(this.trigger)
			.mousedown(e => {
				e.preventDefault();
				const $el = $(e.target).parent(this.selector).addClass(cls);
                if($el.is(':visible')) {
                    const height = $el.outerHeight(),
                        width = $el.outerWidth();
                    var ypos = $el.offset().top + height - e.pageY,
                        xpos = $el.offset().left + width - e.pageX;

                    $(document).mousemove(e => {
                        e.preventDefault();
                        var itop = e.pageY + ypos - height,
                            ileft = e.pageX + xpos - width;
                        if ($el.hasClass(cls)) {
                            $el.offset({top: itop, left: ileft});
                        }
                    }).mouseup(() => {
                        $el.removeClass(cls)
                    });
                }
			});
	}
}

export default Draggable
