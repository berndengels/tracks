var DateMixin = {
	created: function () {
		moment.locale('de');
	},
	methods: {
		formatDate(value, format = 'DD.MM.YYYY') {
			if(value) {
				return moment(String(value)).format(format)
			}
			return null
		},
		formatDateTime(value, format = 'DD.MM.YYYY hh:ii:ss') {
			if(value) {
				return moment(String(value)).format(format)
			}
			return null
		},
		formatNow(format = 'DD.MM.YYYY hh:ii:ss') {
			return moment().format(format)
		},
		formatDay(value, format = 'dd DD.MM.YYYY') {
			if (value) {
				return moment(String(value)).format(format)
			}
			return null
		},
		formatDayShort(value) {
			if (value) {
				return moment(String(value)).format('DD.MM.YY')
			}
			return null
		},
		isBetween(date, from, until) {
			return moment(date).isBetween(moment(from), moment(until), 'days', '[]') || moment(date).isBefore(moment(from), 'days', [])
		},
		isBefore(date, from) {
			return moment(date).isBefore(moment(from),'days')
		}
	},
}
export default DateMixin;
