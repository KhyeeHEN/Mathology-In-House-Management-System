// Calendar functionality for instructor dashboard - View Only
class InstructorCalendar {
    constructor() {
        this.currentDate = new Date();
        this.events = window.calendarEvents || [];
        this.init();
    }

    init() {
        this.renderCalendar();
        this.bindEvents();
        this.createModal();
        this.showViewOnlyMessage();
    }

    showViewOnlyMessage() {
        const calendarContainer = document.querySelector('.calendar-container');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'view-only-message';
        messageDiv.innerHTML = `
            <i class="fas fa-info-circle"></i>
            <p>This calendar displays your scheduled classes. View class details by clicking on any event.</p>
        `;
        calendarContainer.insertBefore(messageDiv, calendarContainer.firstChild);
    }

    bindEvents() {
        // Navigation buttons
        document.getElementById('prevMonth').addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.renderCalendar();
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.renderCalendar();
        });

        // Date picker
        const datePicker = document.getElementById('datePicker');
        if (datePicker) {
            datePicker.addEventListener('change', (e) => {
                this.currentDate = new Date(e.target.value);
                this.renderCalendar();
            });
        }

        // Close modal events
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
    }

    renderCalendar() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        // Update header
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
        
        // Update date picker
        const datePicker = document.getElementById('datePicker');
        if (datePicker) {
            datePicker.value = this.formatDateForInput(this.currentDate);
        }

        // Calculate first day and number of days
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startingDayOfWeek = firstDay.getDay();
        const daysInMonth = lastDay.getDate();

        // Clear existing calendar
        const calendarDays = document.getElementById('calendarDays');
        calendarDays.innerHTML = '';

        // Add empty cells for days before the first day of the month
        for (let i = 0; i < startingDayOfWeek; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.classList.add('calendar-day', 'other-month');
            calendarDays.appendChild(emptyDay);
        }

        // Add days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = this.createDayElement(day, month, year);
            calendarDays.appendChild(dayElement);
        }
    }

    createDayElement(day, month, year) {
        const dayElement = document.createElement('div');
        dayElement.classList.add('calendar-day');
        
        const currentDate = new Date(year, month, day);
        const today = new Date();
        
        // Check if it's today
        if (this.isSameDay(currentDate, today)) {
            dayElement.classList.add('today');
        }

        // Create day number
        const dayNumber = document.createElement('div');
        dayNumber.classList.add('day-number');
        dayNumber.textContent = day;
        dayElement.appendChild(dayNumber);

        // Find events for this day
        const dayEvents = this.getEventsForDate(currentDate);
        
        if (dayEvents.length > 0) {
            dayElement.classList.add('has-events');
            
            // Create events container
            const eventsContainer = document.createElement('div');
            eventsContainer.classList.add('day-events');
            
            // Add up to 2 events, then show "more" indicator
            const maxVisibleEvents = 2;
            const visibleEvents = dayEvents.slice(0, maxVisibleEvents);
            
            visibleEvents.forEach(event => {
                const eventElement = document.createElement('div');
                eventElement.classList.add('event-item');
                eventElement.textContent = `${event.time} - ${event.title}`;
                eventElement.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.showEventDetails(event, currentDate);
                });
                eventsContainer.appendChild(eventElement);
            });

            // Show "more" indicator if there are additional events
            if (dayEvents.length > maxVisibleEvents) {
                const moreIndicator = document.createElement('div');
                moreIndicator.classList.add('more-events');
                moreIndicator.textContent = `+${dayEvents.length - maxVisibleEvents} more`;
                moreIndicator.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.showDayEvents(dayEvents, currentDate);
                });
                eventsContainer.appendChild(moreIndicator);
            }

            dayElement.appendChild(eventsContainer);
        }

        // Add click event for day
        dayElement.addEventListener('click', () => {
            this.showDayEvents(dayEvents, currentDate);
        });

        return dayElement;
    }

    getEventsForDate(date) {
        const dateString = this.formatDate(date);
        const dayOfWeek = date.toLocaleDateString('en-US', { weekday: 'long' });
        
        return this.events.filter(event => {
            // Match either exact date or recurring day of week
            return event.date === dateString || 
                (event.dayOfWeek && event.dayOfWeek.toLowerCase() === dayOfWeek.toLowerCase());
        });
    }

    showEventDetails(event, date) {
        const modal = document.getElementById('eventModal');
        const modalContent = modal.querySelector('.modal-content');
        
        modalContent.innerHTML = `
            <div class="modal-header">
                <h2>${event.title}</h2>
                <button class="close-btn" onclick="instructorCalendar.closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="event-detail">
                    <div class="detail-row">
                        <strong>Date:</strong>
                        <span>${this.formatDateDisplay(date)}</span>
                    </div>
                    <div class="detail-row">
                        <strong>Time:</strong>
                        <span>${event.time}</span>
                    </div>
                    <div class="detail-row">
                        <strong>Duration:</strong>
                        <span>${event.duration}</span>
                    </div>
                    <div class="detail-row">
                        <strong>Venue:</strong>
                        <span>${event.venue}</span>
                    </div>
                    <div class="detail-row">
                        <strong>Students:</strong>
                        <span class="students-list">${event.students}</span>
                    </div>
                    ${event.description ? `
                        <div class="detail-row">
                            <strong>Description:</strong>
                            <span>${event.description}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    showDayEvents(events, date) {
        if (events.length === 0) {
            this.showNoEventsModal(date);
            return;
        }

        if (events.length === 1) {
            this.showEventDetails(events[0], date);
            return;
        }

        const modal = document.getElementById('eventModal');
        const modalContent = modal.querySelector('.modal-content');
        
        modalContent.innerHTML = `
            <div class="modal-header">
                <h2>Classes on ${this.formatDateDisplay(date)}</h2>
                <button class="close-btn" onclick="instructorCalendar.closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="events-list">
                    ${events.map(event => `
                        <div class="event-card" onclick="instructorCalendar.showEventDetails(${JSON.stringify(event).replace(/"/g, '&quot;')}, new Date('${date}'))">
                            <div class="event-time">${event.time}</div>
                            <div class="event-title">${event.title}</div>
                            <div class="event-duration">${event.duration}</div>
                            <div class="event-students">${event.students}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    showNoEventsModal(date) {
        const modal = document.getElementById('eventModal');
        const modalContent = modal.querySelector('.modal-content');
        
        modalContent.innerHTML = `
            <div class="modal-header">
                <h2>No Classes Scheduled</h2>
                <button class="close-btn" onclick="instructorCalendar.closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="no-events">
                    <i class="fas fa-calendar-times"></i>
                    <p>No classes scheduled for ${this.formatDateDisplay(date)}</p>
                    <p style="font-size: 14px; color: #888; margin-top: 20px;">
                        Classes are scheduled by administrators. Contact your administrator for scheduling changes.
                    </p>
                </div>
            </div>
        `;
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    createModal() {
        // Create modal if it doesn't exist
        if (!document.getElementById('eventModal')) {
            const modal = document.createElement('div');
            modal.id = 'eventModal';
            modal.classList.add('modal-overlay');
            modal.innerHTML = `
                <div class="modal-content">
                    <!-- Modal content will be dynamically inserted here -->
                </div>
            `;
            document.body.appendChild(modal);
        }
    }

    closeModal() {
        const modal = document.getElementById('eventModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Utility functions
    formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    formatDateForInput(date) {
        return date.toISOString().split('T')[0];
    }

    formatDateDisplay(date) {
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        return new Date(date).toLocaleDateString('en-US', options);
    }

    isSameDay(date1, date2) {
        return date1.getDate() === date2.getDate() &&
               date1.getMonth() === date2.getMonth() &&
               date1.getFullYear() === date2.getFullYear();
    }
}

// Initialize calendar when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.instructorCalendar = new InstructorCalendar();
});

// Export for global access
window.InstructorCalendar = InstructorCalendar;