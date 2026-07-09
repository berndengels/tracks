const MathMixin = {
	methods: {
		decimalToDegree(v) {
			if(!v) {
				return null;
			}
			var min = Math.floor(Math.abs(v));
			var sec = Math.floor((Math.abs(v) * 60) % 60);
			return (min < 10 ? "0" : "") + min + ":" + (sec < 10 ? "0" : "") + sec;
		},
		degreeToDecimal(v) {
			if(!v) {
				return null;
			}
			let hoursMinutes = v.split(/[.:]/),
				hours = parseInt(hoursMinutes[0], 10),
				minutes = hoursMinutes[1] ? parseInt(hoursMinutes[1], 10) : 0;

			return hours + minutes / 60;
		},
		currencyFormat(number, currency = '€') {
			return new Intl.NumberFormat("de-DE", { style: "currency", currency: "EUR" }).format(
				number,
			)
		}
	},
}
export default MathMixin;
