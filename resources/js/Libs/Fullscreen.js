class Fullscreen {
	init(url) {
		this.img = document.createElement('img');
		this.img.src = url;
		this.openFullscreen(this.img);
	}

	openFullscreen(img) {
		$(img).appendTo('body');
		if (img.requestFullscreen) {
			img.requestFullscreen();
		} else if (img.webkitRequestFullscreen) { /* Safari */
			img.webkitRequestFullscreen();
		} else if (img.msRequestFullscreen) { /* IE11 */
			img.msRequestFullscreen();
		}
		img.onclick = () => {
			this.closeFullscreen();
			$(this.img).remove();
			this.img = null;
			delete this.img;
		}
	}
	closeFullscreen () {
		if (document.exitFullscreen) {
			document.exitFullscreen();
		} else if (document.webkitExitFullscreen) { /* Safari */
			document.webkitExitFullscreen();
		} else if (document.msExitFullscreen) { /* IE11 */
			document.msExitFullscreen();
		}
	}
}

export default Fullscreen
