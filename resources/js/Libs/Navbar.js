
class Navbar {
	init(currentRouteBase) {
        $(document).ready(() => {
            $('.btn-toggle').click(e => {
                const $current = $(e.target);
                $('li.parent','ul.nav').each((i,li) => {
                    let $toggle = $(li).find('.btn-toggle'),
                        $collapse = $toggle.next('div');
                    if($toggle.data('bs-target') !== $current.data('bs-target')) {
                        $collapse.collapse('hide')
                    }
                });
            });
            $('.nav-link').each((i,a) => {
                let $a = $(a),
                    route = $a.data('route') ?? null,
                    $li = $a.parent('li'),
                    $collapse = $li.parent('ul').parent('div')
                ;
                if(route && route === currentRouteBase) {
                    $li.addClass('active')
                    $collapse.removeClass('collapse')
                } else {
                    $li.removeClass('active')
                }
            });
        });
	}
}
export default Navbar
