// Calendar functionality
class Calendar {
    constructor() {
        this.currentDate = new Date();
        this.events = calendarEvents || [];
        this.initializeCalendar();
        this.setupEventListeners();
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
            const dateString = date.toISOString().split('T')[0];
            const dayElement = this.createDayElement(day);
            
            // Add events for this day
            const dayEvents = this.events.filter(event => event.date === dateString);

            if (dayEvents.length > 0) {
                // Add event indicator
                const eventIndicator = document.createElement('div');
                eventIndicator.className = 'event-indicator';
                eventIndicator.innerHTML = `
                    <i class="fas fa-circle"></i>
                    <span class="event-count">${dayEvents.length}</span>
                `;
                dayElement.appendChild(eventIndicator);
                
                // Add click listener to show class details
                dayElement.addEventListener('click', (e) => this.showClassDetails(e, dateString, dayEvents));
                dayElement.style.cursor = 'pointer';
                dayElement.classList.add('has-events');
            }

            calendarDays.appendChild(dayElement);
        }
    }

    showClassDetails(e, date, events) {
        this.removeCurrentPopover();

        const popover = document.createElement('div');
        popover.className = 'student-list-popover';
        
        // Format date for display
        const displayDate = new Date(date).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        popover.innerHTML = `
            <div class="student-popover-header">
                <h3><i class="fas fa-calendar-day"></i> ${displayDate}</h3>
                <button class="close-popover">×</button>
            </div>
            <div class="student-popover-content">
                <div class="student-count-info">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>${events.length} class${events.length !== 1 ? 'es' : ''} scheduled</span>
                </div>
                <div class="student-list">
                    ${this.renderClassList(events)}
                </div>
            </div>
        `;

        // Position the popover
        const rect = e.target.getBoundingClientRect();
        popover.style.position = 'absolute';
        popover.style.left = `${Math.min(rect.left, window.innerWidth - 400)}px`;
        popover.style.top = `${rect.bottom + 5}px`;
        popover.style.zIndex = '1000';

        document.body.appendChild(popover);
        
        // Add event listeners
        popover.querySelector('.close-popover').addEventListener('click', () => this.removeCurrentPopover());
        
        // Add click outside to close
        setTimeout(() => {
            document.addEventListener('click', this.handleOutsideClick.bind(this), { once: true });
        }, 0);

        this.currentPopover = popover;
    }

    renderClassList(events) {
        return events.map(event => `
            <div class="student-item">
                <div class="student-info">
                    <div class="student-name">
                        <i class="fas fa-book"></i>
                        <strong>${event.title}</strong>
                    </div>
                    <div class="class-details">
                        <div class="time-info">
                            <i class="fas fa-clock"></i>
                            <span>${event.time} (${event.duration})</span>
                        </div>
                        <div class="course-info">
                            <i class="fas fa-users"></i>
                            <span>Students: ${event.students}</span>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    handleOutsideClick(event) {
        if (this.currentPopover && !this.currentPopover.contains(event.target)) {
            this.removeCurrentPopover();
        }
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