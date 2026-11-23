
import { Calendar, EventDropArg } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import momentTimezonePlugin from '@fullcalendar/moment-timezone';
import timeGridPlugin from '@fullcalendar/timegrid';

class MyCalendar {
    calendar = null;

	options= {
		plugins: [ dayGridPlugin, timeGridPlugin, momentTimezonePlugin, interactionPlugin ],
        themeSystem: 'bootstrap5',
        dayMaxEvents: true,
		locale: 'de',
        defaultAllDay: true,
		timeZone: 'Europe/Berlin',
		initialView: 'dayGridMonth',
        editable: true,
        droppable: true,
		expandRows: true,
		displayEventTime: false,
		firstDay: 1,
		contentWidth: '100%',
		aspectRatio: 1,
        eventDragStart: this.onDragStart,
        eventDragStop: this.onDragStop,
        eventDrop: ({event, oldEvent, delta}) => {
            delta.days = delta.days * -1;
            this.calendar.getEvents().filter((e) => {
                if(e.startStr === event.startStr && 'project' !== e.extendedProps.type) {
                    console.info("delta", delta)
                    e.moveDates(delta);
                }
                return e;
            });

//            console.info("events", this.options.events)
//        console.info("Drop new", newType)
//        console.info("Drop old", oldType)
        },
        eventClick: this.onEventClick,
        eventConstraint: {
            daysOfWeek: [ 1, 2, 3, 4, 5 ]
        },
		headerToolbar: {
			left: 'prev,next today',
			center: 'title',
			right: 'dayGridMonth,timeGridWeek'
		},
	}

	init(calendarSelector, dates, customOptions = null) {
		if(customOptions) {
			this.options = { ...this.options, ...customOptions }
		}
		this.options.events = dates;

		this.calendar = new Calendar(calendarSelector, this.options);
		this.calendar.render();

		return this.calendar;
	}

    update(events) {
        this.options.events = dates;
        this.calendar.updateEvents(this.options.events)
    }

    onEventClick(args){
        console.info("event click", args.event.extendedProps.type)
    }

    onDragStart(args){
        if("holiday" === args.event.extendedProps.type) {
            alert('Feiertage können  nicht verschoben werden!');
            return false;
        }
    }

    onDragStop(args){
//        console.info("Drag Stp", args)
    }

    onEventDrop = ({event, oldEvent, delta}) => {
        alert('OK')
        console.info("events", this.options.events)
//        let current = this.options.events.filter(e => e.start === event.start);
//        console.info("current", current)
/*
        const
            newType = event.extendedProps.type,
            oldType = oldEvent.extendedProps.type
        ;
*/
//        console.info("Drop new", newType)
//        console.info("Drop old", oldType)
    }
}
export default MyCalendar
