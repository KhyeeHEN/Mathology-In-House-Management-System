// Calendar functionality
class Calendar {
    constructor() {
        this.currentDate = new Date();
        this.events = this.generateSampleEvents();
        this.initializeCalendar();
        this.setupEventListeners();
        this.addEventHoverEffects();
    }

    initializeCalendar() {
        this.updateCalendarHeader();
        this.renderCalendarDays();
        this.updateDatePicker();
    }

    updateDatePicker() {
        const datePicker = document.getElementById('datePicker');
        const year = this.currentDate.getFullYear();
        const month = String(this.currentDate.getMonth() + 1).padStart(2, '0');
        const day = String(this.currentDate.getDate()).padStart(2, '0');
        datePicker.value = `${year}-${month}-${day}`;
    }

    updateCalendarHeader() {
        const monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"];
        document.getElementById('currentMonth').textContent = 
            `${monthNames[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`;
    }

    renderCalendarDays() {
        const calendarDays = document.getElementById('calendarDays');
        calendarDays.innerHTML = '';

        const firstDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
        const lastDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
        const startingDay = firstDay.getDay();
        const totalDays = lastDay.getDate();

        // Previous month's days
        for (let i = 0; i < startingDay; i++) {
            const dayElement = this.createDayElement('');
            dayElement.style.opacity = '0.5';
            calendarDays.appendChild(dayElement);
        }

        // Current month's days
        for (let day = 1; day <= totalDays; day++) {
            const date = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), day);
            const dayElement = this.createDayElement(day);
            
            // Add events for this day
            const dayEvents = this.events.filter(event => {
                const eventDate = new Date(event.date);
                return eventDate.toDateString() === date.toDateString();
            });

            dayEvents.forEach(event => {
                const eventElement = document.createElement('div');
                eventElement.className = `calendar-event event-${event.type}`;
                eventElement.textContent = event.title;
                dayElement.appendChild(eventElement);
            });

            calendarDays.appendChild(dayElement);
        }
    }

    createDayElement(dayNumber) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        
        if (dayNumber) {
            const numberElement = document.createElement('div');
            numberElement.className = 'day-number';
            numberElement.textContent = dayNumber;
            dayElement.appendChild(numberElement);
        }

        return dayElement;
    }

    addEventHoverEffects() {
        document.addEventListener('mouseover', (e) => {
            if (e.target.classList.contains('calendar-event')) {
                const events = document.querySelectorAll('.calendar-event');
                events.forEach(event => {
                    if (event.textContent === e.target.textContent) {
                        event.style.transform = 'translateX(4px)';
                    }
                });
            }
        });

        document.addEventListener('mouseout', (e) => {
            if (e.target.classList.contains('calendar-event')) {
                const events = document.querySelectorAll('.calendar-event');
                events.forEach(event => {
                    event.style.transform = '';
                });
            }
        });
    }

    generateSampleEvents() {
        const currentYear = this.currentDate.getFullYear();
        const currentMonth = this.currentDate.getMonth();
        
        return [
            {
                title: 'Mathematics Lecture',
                date: new Date(currentYear, currentMonth, 5),
                type: '1'
            },
            {
                title: 'Physics Lab',
                date: new Date(currentYear, currentMonth, 5),
                type: '2'
            },
            {
                title: 'Chemistry Quiz',
                date: new Date(currentYear, currentMonth, 12),
                type: '3'
            },
            {
                title: 'English Essay Due',
                date: new Date(currentYear, currentMonth, 15),
                type: '1'
            },
            {
                title: 'Biology Project',
                date: new Date(currentYear, currentMonth, 20),
                type: '2'
            }
        ];
    }

     setupEventListeners() {
        document.getElementById('prevMonth').addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.initializeCalendar();
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.initializeCalendar();
        });

        document.getElementById('datePicker').addEventListener('change', (e) => {
            this.currentDate = new Date(e.target.value);
            this.initializeCalendar();
        });

        document.getElementById('goToDate').addEventListener('click', () => {
            const datePicker = document.getElementById('datePicker');
            this.currentDate = new Date(datePicker.value);
            this.initializeCalendar();
        });
    }
}

// Initialize calendar
const calendar = new Calendar();