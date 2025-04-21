// Calendar functionality
class Calendar {
    constructor() {
        this.currentDate = new Date();
        this.events = calendarEvents || [];
        this.initializeCalendar();
        this.setupEventListeners();
        this.currentPopover = null;
        
        console.log('Calendar initialized with date:', this.currentDate); // Debug
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
            const dateString = date.toISOString().split('T')[0]; // Format as YYYY-MM-DD
            const dayElement = this.createDayElement(day);
            
            // Add events for this day
            const dayEvents = this.events.filter(event => {
                return event.date === dateString;
            });

            if (dayEvents.length > 0) {
                console.log(`Events found for ${dateString}:`, dayEvents); // Debug
            }

            dayEvents.forEach(event => {
                const eventElement = document.createElement('div');
                eventElement.className = `calendar-event event-${event.type}`;
                eventElement.textContent = `${event.time} - ${event.title}`;
                eventElement.addEventListener('click', (e) => this.showEventPopover(e, event));
                dayElement.appendChild(eventElement);
            });

            calendarDays.appendChild(dayElement);
        }
    }

    showEventPopover(e, event) {
        this.removeCurrentPopover();

        const popover = document.createElement('div');
        popover.className = 'event-popover';
        
        popover.innerHTML = `
            <div class="event-popover-header">
                <h3>${event.title}</h3>
                <button class="close-popover">×</button>
            </div>
            <div class="event-popover-content">
                <div class="event-detail">
                    <i class="fas fa-clock"></i>
                    <span>${event.time} (${event.duration})</span>
                </div>
                <div class="event-detail">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>${event.venue}</span>
                </div>
                <div class="event-detail">
                    <i class="fas fa-user"></i>
                    <span>${event.lecturer}</span>
                </div>
                ${event.description ? `
                <div class="event-detail">
                    <i class="fas fa-info-circle"></i>
                    <span>${event.description}</span>
                </div>` : ''}
            </div>
        `;

        const rect = e.target.getBoundingClientRect();
        popover.style.position = 'absolute';
        popover.style.left = `${rect.left}px`;
        popover.style.top = `${rect.bottom + 5}px`;

        document.body.appendChild(popover);
        popover.querySelector('.close-popover').addEventListener('click', () => this.removeCurrentPopover());
        this.currentPopover = popover;
    }

    removeCurrentPopover() {
        if (this.currentPopover) {
            this.currentPopover.remove();
            this.currentPopover = null;
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
    }
}

// Initialize calendar when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const calendar = new Calendar();
});