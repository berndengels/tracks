var DateCalcMixin = {
	data() {
		return {
			countMaxDays: parseInt(process.env.MIX_MIN_COUNT_COURSE_DAYS),
		}
	},
	created: function () {
		moment.locale('de');
	},
	methods: {
		isWeekend(date) {
			return -1 !== $.inArray(moment(date).weekday(), [5, 6]);
		},

		isFriday(date) {
			return 4 === moment(date).weekday();
		},

		isWeekday(date) {
			return !this.isWeekend(date);
		},

		getEvents(start, ende = null) {
			start = moment(start);

			if (ende) {
				ende = moment(ende);
			}

			var counter = 1,
				index = 1,
				dates = [];

			while (counter <= this.countMaxDays) {
				let next = 1 === counter ? start : start.add(1, 'd'),
					holiday = this.holidayData[next.format('YYYY-MM-DD')] ?? null,
					freeday = this.freedayData[next.format('YYYY-MM-DD')] ?? null,
					isStart = 1 === counter;

				if (this.isWeekend(next)) {
					continue;
				} else {
					if (holiday) {
						dates.push({
							groupId: 1,
							start: moment(holiday.datum).format('YYYY-MM-DD'),
							title: holiday.name,
							textColor: '#fff',
							backgroundColor: isStart ? '#f00' : '#00f',
							extendedProps: {
								isStart: isStart,
								type: holiday.type,
							}
						});
					}
					else if(freeday) {
						dates.push({
							groupId: 1,
							start: moment(freeday.date).format('YYYY-MM-DD'),
							title: 'Unterrichtsfreier Tag',
							textColor: '#fff',
							backgroundColor: isStart ? '#f00' : '#00f',
							extendedProps: {
								isStart: isStart,
								type: 'free',
							}
						});
					}
					else {
						dates.push({
							groupId: 1,
							start: next.format('YYYY-MM-DD'),
							title: 'Unterricht (' + counter + ')',
							textColor: '#fff',
							backgroundColor: isStart ? '#f00' : '#080',
							extendedProps: {
								isStart: isStart,
								type: 'normal',
							}
						});
						counter++;
					}
				}
				if (ende && ende.isSame(next)) {
					break;
				}
				index++;
			}

			return dates;
		},

		updateEvents(events, date, type) {
			date = moment(date);
			var counter = 1, index = 0, dates = [];

			while (counter <= this.countMaxDays) {
				let event = events[index] ?? null,
					eventStart = event ? moment(event.start) : null,
					holiday = event ? (this.holidayData[eventStart.format('YYYY-MM-DD')] ?? null) : null,
					isStart = 1 === counter;

				if(holiday) {
					dates.push({
						groupId: 1,
						resourceId: 'a',
						start: moment(holiday.datum).format('YYYY-MM-DD'),
						title: holiday.name,
						textColor: '#fff',
						backgroundColor: isStart ? '#f00' : '#00f',
						extendedProps: {
							isStart: isStart,
							type: holiday.type,
						}
					});
				} else {
					if(eventStart.isSame(date)) {
						if('normal' === type) {
							dates.push({
								groupId: 1,
								resourceId: 'a',
								start: date.format('YYYY-MM-DD'),
								title: 'Unterricht (' + counter + ')',
								textColor: '#fff',
								backgroundColor: isStart ? '#f00' : '#080',
								extendedProps: {
									isStart: isStart,
									type: 'normal',
								}
							});
							counter++;
						} else {
							dates.push({
								groupId: 1,
								resourceId: 'a',
								start: date.format('YYYY-MM-DD'),
								title: 'Unterrichtsfrei',
								textColor: '#fff',
								backgroundColor: isStart ? '#f00' : '#00f',
								extendedProps: {
									isStart: isStart,
									type: type,
								}
							});
						}
					} else {
						dates.push({
							groupId: 1,
							resourceId: 'a',
							start: eventStart.format('YYYY-MM-DD'),
							title: 'Unterricht (' + counter + ')',
							textColor: '#fff',
							backgroundColor: isStart ? '#f00' : '#080',
							extendedProps: {
								isStart: isStart,
								type: 'normal',
							}
						});
						counter++;
					}
					index++;
				}
			}

			return dates;
		}
	},
}
export default DateCalcMixin;