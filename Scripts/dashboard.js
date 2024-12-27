// Calendar functionality
class Calendar {
    constructor() {
        this.currentDate = new Date();
        this.events = this.generateSampleEvents();
        this.initializeCalendar();
        this.setupEventListeners();
        this.addEventHoverEffects();
        this.currentPopover = null;
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
                eventElement.addEventListener('click', (e) => this.showEventPopover(e, event));
                dayElement.appendChild(eventElement);
            });

            calendarDays.appendChild(dayElement);
        }
    }

    showEventPopover(e, event) {
        // Remove existing popover if any
        this.removeCurrentPopover();

        // Create popover element
        const popover = document.createElement('div');
        popover.className = 'event-popover';
        
        // Format time
        const timeStr = event.time || '09:00 AM'; // Default time if not specified
        const durationStr = event.duration || '1 hour'; // Default duration if not specified
        
        popover.innerHTML = `
            <div class="event-popover-header event-${event.type}">
                <h3>${event.title}</h3>
                <button class="close-popover">×</button>
            </div>
            <div class="event-popover-content">
                <div class="event-detail">
                    <i class="fas fa-clock"></i>
                    <span>${timeStr} (${durationStr})</span>
                </div>
                <div class="event-detail">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>${event.venue || 'Room 101'}</span>
                </div>
                <div class="event-detail">
                    <i class="fas fa-user"></i>
                    <span>${event.lecturer || 'Prof. Smith'}</span>
                </div>
                ${event.description ? `
                <div class="event-detail">
                    <i class="fas fa-info-circle"></i>
                    <span>${event.description}</span>
                </div>` : ''}
            </div>
        `;

        // Position the popover
        const rect = e.target.getBoundingClientRect();
        popover.style.position = 'fixed';
        popover.style.left = `${rect.right + 10}px`;
        popover.style.top = `${rect.top}px`;

        // Add close button listener
        document.body.appendChild(popover);
        const closeBtn = popover.querySelector('.close-popover');
        closeBtn.addEventListener('click', () => this.removeCurrentPopover());

        // Store current popover reference
        this.currentPopover = popover;

        // Close popover when clicking outside
        document.addEventListener('click', this.handleClickOutside);
    }

    handleClickOutside = (e) => {
        if (this.currentPopover && !this.currentPopover.contains(e.target) && 
            !e.target.classList.contains('calendar-event')) {
            this.removeCurrentPopover();
        }
    }

    removeCurrentPopover() {
        if (this.currentPopover) {
            this.currentPopover.remove();
            this.currentPopover = null;
            document.removeEventListener('click', this.handleClickOutside);
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
                type: '1',
                time: '09:00 AM',
                duration: '1.5 hours',
                venue: 'Room 201',
                lecturer: 'Prof. Johnson',
                description: 'Advanced Calculus: Differential Equations'
            },
            {
                title: 'Physics Lab',
                date: new Date(currentYear, currentMonth, 5),
                type: '2',
                time: '02:00 PM',
                duration: '2 hours',
                venue: 'Physics Lab B',
                lecturer: 'Dr. Martinez',
                description: 'Experimental Methods in Wave Mechanics'
            },
            {
                title: 'Chemistry Quiz',
                date: new Date(currentYear, currentMonth, 12),
                type: '3',
                time: '10:30 AM',
                duration: '1 hour',
                venue: 'Room 305',
                lecturer: 'Dr. Chen',
                description: 'Organic Chemistry Assessment'
            },
            {
                title: 'English Essay Due',
                date: new Date(currentYear, currentMonth, 15),
                type: '1',
                time: '11:00 AM',
                duration: '30 minutes',
                venue: 'Room 102',
                lecturer: 'Prof. Williams',
                description: 'Submit your comparative literature essays'
            },
            {
                title: 'Biology Project',
                date: new Date(currentYear, currentMonth, 20),
                type: '2',
                time: '01:30 PM',
                duration: '2 hours',
                venue: 'Biology Lab A',
                lecturer: 'Dr. Thompson',
                description: 'Group presentation on cellular biology'
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