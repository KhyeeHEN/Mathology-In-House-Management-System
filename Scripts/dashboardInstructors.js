// Calendar functionality for instructor dashboard - View Only
class InstructorCalendar {
    constructor() {
        this.currentDate = new Date();
        this.events = [];

        // Load events from global variable
        this.loadEvents();
        this.init();
    }

    loadEvents() {
        if (window.calendarEvents && Array.isArray(window.calendarEvents)) {
            this.events = window.calendarEvents;
            console.log('Successfully loaded events:', this.events.length);
            console.log('Sample event:', this.events[0]);
        } else {
            console.warn('Warning: window.calendarEvents is undefined or not an array. No events loaded.');
            console.log('window.calendarEvents:', window.calendarEvents);
        }
    }

    init() {
        this.renderCalendar();
        this.bindEvents();
        this.createModal();
        this.showViewOnlyMessage();
    }

    showViewOnlyMessage() {
        const calendarContainer = document.querySelector('.calendar-container');
        const existingMessage = calendarContainer.querySelector('.view-only-message');
        
        if (!existingMessage) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'view-only-message';
            messageDiv.innerHTML = `
                <i class="fas fa-info-circle"></i>
                <p>This calendar displays your scheduled classes. View class details by clicking on any event.</p>
            `;
            calendarContainer.insertBefore(messageDiv, calendarContainer.firstChild);
        }
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
        const month = this.currentDate.getMonth(); // Zero-based (e.g., 5 for June)
        
        console.log(`Rendering calendar for: ${year}-${month + 1}`);
        
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
        
        console.log(`Calendar rendered for ${monthNames[month]} ${year}`);
    }

    createDayElement(day, month, year) {
        const dayElement = document.createElement('div');
        dayElement.classList.add('calendar-day');
        
        // Create currentDate with zero-based month (correct)
        const currentDate = new Date(year, month, day);
        // Reset time components to avoid timezone issues
        currentDate.setHours(12, 0, 0, 0);
        
        const today = new Date();
        today.setHours(12, 0, 0, 0); // Also reset time for today
        
        if (this.isSameDay(currentDate, today)) {
            dayElement.classList.add('today');
        }

        const dayNumber = document.createElement('div');
        dayNumber.classList.add('day-number');
        dayNumber.textContent = day;
        dayElement.appendChild(dayNumber);

        // Get events for this date
        const dayEvents = this.getEventsForDate(currentDate);
        
        if (dayEvents.length > 0) {
            console.log(`Day ${day} has ${dayEvents.length} events:`, dayEvents);
            dayElement.classList.add('has-events');
            const eventsContainer = document.createElement('div');
            eventsContainer.classList.add('day-events');
            
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

        dayElement.addEventListener('click', () => {
            this.showDayEvents(dayEvents, currentDate);
        });

        return dayElement;
    }

    getEventsForDate(date) {
        // Create a date string in YYYY-MM-DD format
        const dateString = date.getFullYear() + '-' + 
                         String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                         String(date.getDate()).padStart(2, '0');
        
        const filteredEvents = this.events.filter(event => event.date === dateString);
        
        if (filteredEvents.length > 0) {
            console.log(`Found ${filteredEvents.length} events for ${dateString}:`, filteredEvents);
        }
        
        return filteredEvents;
    }

    showEventDetails(event, date) {
        const modal = document.getElementById('eventModal');
        const modalContent = modal.querySelector('.modal-content');
        
        modalContent.innerHTML = `
            <div class="modal-header">
                <h2>${event.title}</h2>
                <button class="close-btn" onclick="window.instructorCalendar.closeModal()">&times;</button>
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
                <button class="close-btn" onclick="window.instructorCalendar.closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="events-list">
                    ${events.map((event, index) => `
                        <div class="event-card" onclick="window.instructorCalendar.showEventFromList(${index}, '${date}')">
                            <div class="event-time">${event.time}</div>
                            <div class="event-title">${event.title}</div>
                            <div class="event-duration">${event.duration}</div>
                            <div class="event-students">${event.students}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        // Store events temporarily for the modal
        this.modalEvents = events;
        this.modalDate = date;
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    showEventFromList(eventIndex, dateString) {
        const event = this.modalEvents[eventIndex];
        const date = new Date(dateString);
        this.showEventDetails(event, date);
    }

    showNoEventsModal(date) {
        const modal = document.getElementById('eventModal');
        const modalContent = modal.querySelector('.modal-content');
        
        modalContent.innerHTML = `
            <div class="modal-header">
                <h2>No Classes Scheduled</h2>
                <button class="close-btn" onclick="window.instructorCalendar.closeModal()">&times;</button>
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

// Export for global access
window.InstructorCalendar = InstructorCalendar;