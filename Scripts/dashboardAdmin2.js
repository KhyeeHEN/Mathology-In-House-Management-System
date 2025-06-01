// Enhanced Schedule Calendar for Dashboard
class ScheduleCalendar {
    constructor() {
        this.currentWeek = new Date();
        this.timeSlots = this.generateTimeSlots();
        this.classes = calendarEvents || []; // Use the PHP data
        this.currentTooltip = null;
        this.viewMode = 'week'; // 'week' or 'month'
        
        console.log('Schedule Calendar initialized with classes:', this.classes);
        this.init();
    }

    generateTimeSlots() {
        const slots = [];
        // Generate time slots from 8 AM to 6 PM in 30-minute intervals
        for (let hour = 8; hour <= 18; hour++) {
            slots.push(`${hour.toString().padStart(2, '0')}:00`);
            if (hour < 18) {
                slots.push(`${hour.toString().padStart(2, '0')}:30`);
            }
        }
        return slots;
    }

    init() {
        this.setupEventListeners();
        this.renderSchedule();
        this.updateCurrentWeekDisplay();
        this.showCurrentTimeIndicator();
        
        // Update current time indicator every minute
        setInterval(() => this.showCurrentTimeIndicator(), 60000);
    }

    setupEventListeners() {
        // Week navigation
        document.getElementById('prevWeek').addEventListener('click', () => {
            this.currentWeek.setDate(this.currentWeek.getDate() - 7);
            this.updateCurrentWeekDisplay();
            this.renderSchedule();
        });

        document.getElementById('nextWeek').addEventListener('click', () => {
            this.currentWeek.setDate(this.currentWeek.getDate() + 7);
            this.updateCurrentWeekDisplay();
            this.renderSchedule();
        });

        // View toggle
        document.getElementById('weekView').addEventListener('click', (e) => {
            this.switchView('week', e.target);
        });

        document.getElementById('monthView').addEventListener('click', (e) => {
            this.switchView('month', e.target);
        });

        // Click outside to close tooltip
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.class-block') && !e.target.closest('.class-tooltip')) {
                this.hideTooltip();
            }
        });
    }

    switchView(mode, button) {
        this.viewMode = mode;
        document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        if (mode === 'week') {
            this.renderSchedule();
        } else {
            this.renderMonthView();
        }
    }

    updateCurrentWeekDisplay() {
        const startOfWeek = new Date(this.currentWeek);
        startOfWeek.setDate(this.currentWeek.getDate() - this.currentWeek.getDay());
        
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);

        const options = { month: 'short', day: 'numeric' };
        const startStr = startOfWeek.toLocaleDateString('en-US', options);
        const endStr = endOfWeek.toLocaleDateString('en-US', options);
        const year = startOfWeek.getFullYear();

        document.getElementById('currentWeek').textContent = `${startStr} - ${endStr}, ${year}`;
    }

    renderSchedule() {
        const grid = document.getElementById('scheduleGrid');
        grid.innerHTML = '';
        grid.className = 'schedule-grid'; // Reset to weekly grid

        // Create time column
        this.createTimeColumn(grid);

        // Create day columns
        this.createDayColumns(grid);
    }

    createTimeColumn(grid) {
        const timeColumn = document.createElement('div');
        timeColumn.className = 'time-column';

        // Time header
        const timeHeader = document.createElement('div');
        timeHeader.className = 'time-slot time-header';
        timeHeader.innerHTML = '<i class="fas fa-clock"></i>';
        timeColumn.appendChild(timeHeader);

        // Time slots
        this.timeSlots.forEach(time => {
            const timeSlot = document.createElement('div');
            timeSlot.className = 'time-slot';
            timeSlot.textContent = this.formatTime12Hour(time);
            timeColumn.appendChild(timeSlot);
        });

        grid.appendChild(timeColumn);
    }

    createDayColumns(grid) {
        const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const startOfWeek = new Date(this.currentWeek);
        startOfWeek.setDate(this.currentWeek.getDate() - this.currentWeek.getDay());

        daysOfWeek.forEach((day, index) => {
            const dayColumn = document.createElement('div');
            dayColumn.className = 'day-column';
            dayColumn.setAttribute('data-day', day);

            const currentDate = new Date(startOfWeek);
            currentDate.setDate(startOfWeek.getDate() + index);

            // Day header
            const dayHeader = document.createElement('div');
            dayHeader.className = 'day-header';
            const isToday = this.isToday(currentDate);
            if (isToday) dayHeader.classList.add('today');
            
            dayHeader.innerHTML = `
                <div class="day-name">${day.substring(0, 3)}</div>
                <div class="day-date">${currentDate.getDate()}</div>
                ${isToday ? '<div class="today-indicator"></div>' : ''}
            `;
            dayColumn.appendChild(dayHeader);

            // Time slots for this day
            this.timeSlots.forEach((time, timeIndex) => {
                const timeSlot = document.createElement('div');
                timeSlot.className = 'time-grid-slot';
                timeSlot.setAttribute('data-time', time);
                
                // Find classes for this day and time
                const dayClasses = this.getClassesForDayAndTime(day, time, currentDate);
                
                if (dayClasses.length > 0) {
                    dayClasses.forEach(classInfo => {
                        const classBlock = this.createClassBlock(classInfo, currentDate);
                        timeSlot.appendChild(classBlock);
                    });
                } else if (timeIndex % 2 === 0) { // Show placeholder every hour
                    timeSlot.innerHTML = '<div class="empty-slot"></div>';
                }

                dayColumn.appendChild(timeSlot);
            });

            grid.appendChild(dayColumn);
        });
    }

    getClassesForDayAndTime(dayName, time, date) {
        return this.classes.filter(classInfo => {
            // Convert the class date to check if it matches current week's date for this day
            const classDate = new Date(classInfo.date);
            const isSameDate = classDate.toDateString() === date.toDateString();
            
            if (!isSameDate) return false;
            
            // Parse time from class info (assuming format "HH:MM AM/PM")
            const classTime = this.parseTime12Hour(classInfo.time);
            const slotTime = this.timeToMinutes(time);
            
            // Get duration in minutes
            const durationMatch = classInfo.duration.match(/(\d+)\s*hours?\s*(\d*)\s*minutes?/i);
            let durationMinutes = 60; // Default 1 hour
            
            if (durationMatch) {
                const hours = parseInt(durationMatch[1]) || 0;
                const minutes = parseInt(durationMatch[2]) || 0;
                durationMinutes = hours * 60 + minutes;
            }
            
            const classEnd = classTime + durationMinutes;
            
            return slotTime >= classTime && slotTime < classEnd;
        });
    }

    createClassBlock(classInfo, date) {
        const block = document.createElement('div');
        const courseType = this.getCourseType(classInfo.title);
        block.className = `class-block ${courseType}`;
        
        // Calculate height based on duration
        const durationMatch = classInfo.duration.match(/(\d+)\s*hours?\s*(\d*)\s*minutes?/i);
        let durationMinutes = 60; // Default 1 hour
        
        if (durationMatch) {
            const hours = parseInt(durationMatch[1]) || 0;
            const minutes = parseInt(durationMatch[2]) || 0;
            durationMinutes = hours * 60 + minutes;
        }
        
        const height = Math.max(50, (durationMinutes / 30) * 60 - 8);
        block.style.height = `${height}px`;
        
        // Extract course name and instructor
        const [courseName, instructor] = classInfo.title.split(' - ');
        
        block.innerHTML = `
            <div class="class-title">${courseName}</div>
            <div class="class-instructor">${instructor || 'Unknown Instructor'}</div>
            <div class="class-time">${classInfo.time}</div>
            <div class="class-students-count">${this.getStudentCount(classInfo.students)} students</div>
        `;

        // Add click event for detailed view
        block.addEventListener('click', (e) => {
            e.stopPropagation();
            this.showClassDetails(e, classInfo, date);
        });

        // Add hover effects
        block.addEventListener('mouseenter', (e) => this.showQuickTooltip(e, classInfo));
        block.addEventListener('mouseleave', () => this.hideQuickTooltip());

        return block;
    }

    getCourseType(title) {
        const course = title.toLowerCase();
        if (course.includes('math')) return 'math';
        if (course.includes('science') || course.includes('physics') || course.includes('chemistry') || course.includes('biology')) return 'science';
        if (course.includes('english') || course.includes('literature') || course.includes('language')) return 'english';
        if (course.includes('history') || course.includes('social')) return 'history';
        if (course.includes('art') || course.includes('music') || course.includes('drama')) return 'art';
        if (course.includes('computer') || course.includes('programming') || course.includes('coding')) return 'computer';
        return 'general';
    }

    getStudentCount(studentsString) {
        if (!studentsString) return 0;
        return studentsString.split(',').length;
    }

    showClassDetails(event, classInfo, date) {
        this.hideTooltip(); // Hide any existing tooltip
        
        const modal = this.createClassModal(classInfo, date);
        document.body.appendChild(modal);
        
        // Show modal with animation
        setTimeout(() => modal.classList.add('show'), 10);
        
        // Close modal on outside click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeModal(modal);
            }
        });
    }

    createClassModal(classInfo, date) {
        const modal = document.createElement('div');
        modal.className = 'class-modal';
        
        const [courseName, instructor] = classInfo.title.split(' - ');
        const students = classInfo.students ? classInfo.students.split(', ') : [];
        
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-graduation-cap"></i> ${courseName}</h2>
                    <button class="close-modal"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <div class="class-info-grid">
                        <div class="info-item">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <div>
                                <label>Instructor</label>
                                <span>${instructor || 'Not assigned'}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <label>Time</label>
                                <span>${classInfo.time} (${classInfo.duration})</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <label>Date</label>
                                <span>${this.formatDate(date)}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <label>Students</label>
                                <span>${students.length} enrolled</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="students-section">
                        <h3><i class="fas fa-user-graduate"></i> Enrolled Students</h3>
                        <div class="students-list">
                            ${students.map(student => `
                                <div class="student-card">
                                    <div class="student-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="student-info">
                                        <span class="student-name">${student.trim()}</span>
                                    </div>
                                    <div class="student-actions">
                                        <button class="btn-sm" onclick="viewStudentDetails('${student.trim()}')">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary" onclick="this.closest('.class-modal').remove()">Close</button>
                    <button class="btn-primary" onclick="editClass('${classInfo.title}')">
                        <i class="fas fa-edit"></i> Edit Class
                    </button>
                </div>
            </div>
        `;
        
        // Add close button functionality
        modal.querySelector('.close-modal').addEventListener('click', () => {
            this.closeModal(modal);
        });
        
        return modal;
    }

    closeModal(modal) {
        modal.classList.add('closing');
        setTimeout(() => modal.remove(), 300);
    }

    showQuickTooltip(event, classInfo) {
        const tooltip = document.getElementById('classTooltip') || this.createTooltipElement();
        const [courseName, instructor] = classInfo.title.split(' - ');
        const studentCount = this.getStudentCount(classInfo.students);
        
        tooltip.innerHTML = `
            <div class="tooltip-title">${courseName}</div>
            <div class="tooltip-info"><i class="fas fa-chalkboard-teacher"></i> ${instructor || 'No instructor'}</div>
            <div class="tooltip-info"><i class="fas fa-clock"></i> ${classInfo.time} (${classInfo.duration})</div>
            <div class="tooltip-info"><i class="fas fa-users"></i> ${studentCount} student${studentCount !== 1 ? 's' : ''}</div>
            <div class="tooltip-hint">Click for details</div>
        `;

        this.positionTooltip(tooltip, event.target);
        tooltip.classList.add('show');
    }

    createTooltipElement() {
        const tooltip = document.createElement('div');
        tooltip.id = 'classTooltip';
        tooltip.className = 'class-tooltip';
        document.body.appendChild(tooltip);
        return tooltip;
    }

    positionTooltip(tooltip, target) {
        const rect = target.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        let left = rect.right + 10;
        let top = rect.top;
        
        // Adjust if tooltip goes off screen
        if (left + tooltipRect.width > window.innerWidth) {
            left = rect.left - tooltipRect.width - 10;
        }
        
        if (top + tooltipRect.height > window.innerHeight) {
            top = window.innerHeight - tooltipRect.height - 10;
        }
        
        tooltip.style.left = `${left}px`;
        tooltip.style.top = `${top}px`;
    }

    hideQuickTooltip() {
        const tooltip = document.getElementById('classTooltip');
        if (tooltip) {
            tooltip.classList.remove('show');
        }
    }

    hideTooltip() {
        this.hideQuickTooltip();
    }

    renderMonthView() {
        // Implementation for month view (similar to original calendar)
        const grid = document.getElementById('scheduleGrid');
        grid.innerHTML = '';
        grid.className = 'calendar-month-grid';
        
        // Create month calendar structure
        this.renderMonthCalendar(grid);
    }

    renderMonthCalendar(grid) {
        const monthContainer = document.createElement('div');
        monthContainer.className = 'month-calendar-container';
        
        // Calendar weekdays header
        const weekdaysHeader = document.createElement('div');
        weekdaysHeader.className = 'calendar-weekdays';
        ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
            const dayElement = document.createElement('div');
            dayElement.textContent = day;
            weekdaysHeader.appendChild(dayElement);
        });
        monthContainer.appendChild(weekdaysHeader);
        
        // Calendar days
        const daysContainer = document.createElement('div');
        daysContainer.className = 'calendar-days';
        
        const currentMonth = this.currentWeek.getMonth();
        const currentYear = this.currentWeek.getFullYear();
        const firstDay = new Date(currentYear, currentMonth, 1);
        const lastDay = new Date(currentYear, currentMonth + 1, 0);
        const startingDay = firstDay.getDay();
        const totalDays = lastDay.getDate();

        // Previous month's days
        for (let i = 0; i < startingDay; i++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day prev-month';
            daysContainer.appendChild(dayElement);
        }

        // Current month's days
        for (let day = 1; day <= totalDays; day++) {
            const date = new Date(currentYear, currentMonth, day);
            const dateString = date.toISOString().split('T')[0];
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            
            if (this.isToday(date)) {
                dayElement.classList.add('today');
            }
            
            dayElement.innerHTML = `<div class="day-number">${day}</div>`;
            
            // Find events for this day
            const dayEvents = this.classes.filter(event => event.date === dateString);
            
            if (dayEvents.length > 0) {
                const eventIndicator = document.createElement('div');
                eventIndicator.className = 'event-indicator';
                eventIndicator.innerHTML = `
                    <i class="fas fa-circle"></i>
                    <span class="event-count">${dayEvents.length}</span>
                `;
                dayElement.appendChild(eventIndicator);
                
                dayElement.addEventListener('click', (e) => {
                    this.showDayEvents(e, date, dayEvents);
                });
                dayElement.classList.add('has-events');
            }
            
            daysContainer.appendChild(dayElement);
        }
        
        monthContainer.appendChild(daysContainer);
        grid.appendChild(monthContainer);
    }

    showDayEvents(event, date, events) {
        // Similar to original showStudentList functionality but updated
        const modal = this.createDayEventsModal(date, events);
        document.body.appendChild(modal);
        setTimeout(() => modal.classList.add('show'), 10);
    }

    createDayEventsModal(date, events) {
        const modal = document.createElement('div');
        modal.className = 'class-modal';
        
        const displayDate = date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-calendar-day"></i> ${displayDate}</h2>
                    <button class="close-modal"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <div class="day-events-list">
                        ${events.map(event => {
                            const [courseName, instructor] = event.title.split(' - ');
                            return `
                                <div class="event-card">
                                    <div class="event-time">${event.time}</div>
                                    <div class="event-details">
                                        <h4>${courseName}</h4>
                                        <p><i class="fas fa-chalkboard-teacher"></i> ${instructor}</p>
                                        <p><i class="fas fa-clock"></i> ${event.duration}</p>
                                        <p><i class="fas fa-users"></i> ${this.getStudentCount(event.students)} students</p>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary" onclick="this.closest('.class-modal').remove()">Close</button>
                </div>
            </div>
        `;
        
        modal.querySelector('.close-modal').addEventListener('click', () => {
            this.closeModal(modal);
        });
        
        return modal;
    }

    showCurrentTimeIndicator() {
        // Remove existing indicators
        document.querySelectorAll('.current-time-indicator').forEach(indicator => {
            indicator.remove();
        });
        
        if (this.viewMode !== 'week') return;
        
        const now = new Date();
        const currentTime = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
        const currentMinutes = this.timeToMinutes(currentTime);
        
        // Only show if within schedule hours
        if (currentMinutes >= this.timeToMinutes('08:00') && currentMinutes <= this.timeToMinutes('18:00')) {
            const firstTimeSlot = this.timeToMinutes(this.timeSlots[0]);
            const position = ((currentMinutes - firstTimeSlot) / 30) * 60 + 60; // +60 for header
            
            const dayColumns = document.querySelectorAll('.day-column');
            const currentDay = now.getDay();
            
            if (dayColumns[currentDay]) {
                const indicator = document.createElement('div');
                indicator.className = 'current-time-indicator';
                indicator.style.top = `${position}px`;
                indicator.innerHTML = '<div class="current-time-dot"></div>';
                dayColumns[currentDay].appendChild(indicator);
            }
        }
    }

    // Utility functions
    parseTime12Hour(time12) {
        const [time, ampm] = time12.split(' ');
        const [hours, minutes] = time.split(':').map(Number);
        let hours24 = hours;
        
        if (ampm === 'PM' && hours !== 12) {
            hours24 += 12;
        } else if (ampm === 'AM' && hours === 12) {
            hours24 = 0;
        }
        
        return hours24 * 60 + minutes;
    }

    timeToMinutes(time) {
        const [hours, minutes] = time.split(':').map(Number);
        return hours * 60 + minutes;
    }

    formatTime12Hour(time24) {
        const [hours, minutes] = time24.split(':').map(Number);
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const hours12 = hours % 12 || 12;
        return `${hours12}:${minutes.toString().padStart(2, '0')} ${ampm}`;
    }

    formatDate(date) {
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    isToday(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    }
}

// Global functions for external use
function viewStudentDetails(studentName) {
    console.log('Viewing details for student:', studentName);
    // Implement based on your needs - could open modal, redirect, etc.
    // Example: window.location.href = `student-profile.php?name=${encodeURIComponent(studentName)}`;
}

function editClass(classTitle) {
    console.log('Editing class:', classTitle);
    // Implement class editing functionality
    // Example: window.location.href = `edit-class.php?class=${encodeURIComponent(classTitle)}`;
}

// Initialize calendar when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Wait a bit to ensure calendarEvents is available
    setTimeout(() => {
        window.scheduleCalendar = new ScheduleCalendar();
    }, 100);
});