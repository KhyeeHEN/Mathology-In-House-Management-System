// Enhanced Schedule Calendar Class
class ScheduleCalendar {
    constructor() {
        this.currentWeek = new Date();
        this.currentDate = new Date();
        this.timeSlots = this.generateTimeSlots();
        this.currentTooltip = null;
        this.currentView = 'day'; // Track current view
        
        // Convert PHP classes data to JavaScript format
        this.classes = window.rosterClasses || [];
        
        this.init();
    }

    generateTimeSlots() {
        const slots = [];
        for (let hour = 8; hour <= 23; hour++) { // Changed from 17 to 23 for 11pm
            slots.push(`${hour.toString().padStart(2, '0')}:00`);
            if (hour < 23) { // Don't add :30 for 11pm
                slots.push(`${hour.toString().padStart(2, '0')}:30`);
            }
        }
        return slots;
    }

    init() {
        this.setupEventListeners();
        if (this.currentView === 'week') {
            this.renderWeekView();
        } else {
            this.renderDayView();
        }
        this.updateCurrentWeekDisplay();
        this.showCurrentTimeIndicator();
    }

    setupEventListeners() {
        // Week navigation
        document.getElementById('prevWeek')?.addEventListener('click', () => {
            if (this.currentView === 'week') {
                this.currentWeek.setDate(this.currentWeek.getDate() - 7);
                this.updateCurrentWeekDisplay();
                this.renderWeekView();
            } else {
                this.currentDate.setDate(this.currentDate.getDate() - 1);
                this.updateCurrentDateDisplay();
                this.renderDayView();
            }
        });

        document.getElementById('nextWeek')?.addEventListener('click', () => {
            if (this.currentView === 'week') {
                this.currentWeek.setDate(this.currentWeek.getDate() + 7);
                this.updateCurrentWeekDisplay();
                this.renderWeekView();
            } else {
                this.currentDate.setDate(this.currentDate.getDate() + 1);
                this.updateCurrentDateDisplay();
                this.renderDayView();
            }
        });

        // View toggle listeners
        document.getElementById('dayView')?.addEventListener('click', () => {
            this.switchToDay();
        });

        document.getElementById('weekView')?.addEventListener('click', () => {
            this.switchToWeek();
        });

        // Toggle class details
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('toggle-details-btn')) {
                const button = e.target;
                const details = button.previousElementSibling;
                button.classList.toggle('active');
                details.classList.toggle('show');
                
                if (details.classList.contains('show')) {
                    button.innerHTML = 'Hide Details <i class="fas fa-chevron-up"></i>';
                } else {
                    button.innerHTML = 'Show Details <i class="fas fa-chevron-down"></i>';
                }
            }
        });
    }

    switchToDay() {
        this.currentView = 'day';
        
        // Update button states
        document.getElementById('dayView')?.classList.add('active');
        document.getElementById('weekView')?.classList.remove('active');
        
        // Show/hide appropriate containers
        const dayContainer = document.querySelector('.current-day-roster');
        const weekContainer = document.querySelector('.week-view-roster');
        
        if (dayContainer) dayContainer.style.display = 'block';
        if (weekContainer) weekContainer.style.display = 'none';
        
        // Update navigation labels
        this.updateNavigationLabels();
        this.renderDayView();
        this.updateCurrentDateDisplay();
    }

    switchToWeek() {
        this.currentView = 'week';
        
        // Update button states
        document.getElementById('weekView')?.classList.add('active');
        document.getElementById('dayView')?.classList.remove('active');
        
        // Show/hide appropriate containers
        const dayContainer = document.querySelector('.current-day-roster');
        const weekContainer = document.querySelector('.week-view-roster');
        
        if (dayContainer) dayContainer.style.display = 'none';
        if (weekContainer) weekContainer.style.display = 'block';
        
        // Update navigation labels
        this.updateNavigationLabels();
        this.renderWeekView();
        this.updateCurrentWeekDisplay();
    }

    updateNavigationLabels() {
        const prevBtn = document.getElementById('prevWeek');
        const nextBtn = document.getElementById('nextWeek');
        
        if (this.currentView === 'day') {
            if (prevBtn) prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i> Previous Day';
            if (nextBtn) nextBtn.innerHTML = 'Next Day <i class="fas fa-chevron-right"></i>';
        } else {
            if (prevBtn) prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i> Previous Week';
            if (nextBtn) nextBtn.innerHTML = 'Next Week <i class="fas fa-chevron-right"></i>';
        }
    }

    updateCurrentDateDisplay() {
        const options = { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' };
        const dateStr = this.currentDate.toLocaleDateString('en-US', options);
        document.getElementById('currentWeek').textContent = dateStr;
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

    renderDayView() {
        const container = document.querySelector('.current-day-roster .class-cards-container');
        if (!container) return;

        const currentDayName = this.currentDate.toLocaleDateString('en-US', { weekday: 'long' });
        const currentTime = new Date().toTimeString().split(' ')[0]; // HH:MM:SS format
        
        // Update day title
        const dayTitle = document.querySelector('.current-day-roster h3');
        if (dayTitle) {
            dayTitle.textContent = `${currentDayName}'s Classes (${this.currentDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })})`;
        }

        // Filter classes for current day
        const dayClasses = this.classes.filter(classInfo => {
            return classInfo.day === currentDayName;
        });

        container.innerHTML = '';

        if (dayClasses.length === 0) {
            container.innerHTML = '<div class="no-classes">No classes scheduled for this day.</div>';
            return;
        }

        // Sort classes by start time
        dayClasses.sort((a, b) => {
            return this.timeToMinutes(a.startTime) - this.timeToMinutes(b.startTime);
        });

        dayClasses.forEach(classInfo => {
            const startTime = classInfo.startTime;
            const endTime = classInfo.endTime;
            
            // Check if current time is within class time (only for today)
            const isToday = this.currentDate.toDateString() === new Date().toDateString();
            const isCurrent = isToday && (currentTime >= startTime && currentTime <= endTime);
            
            const duration = this.getClassDuration(startTime, endTime);
            
            const classCard = document.createElement('div');
            classCard.className = `class-card ${isCurrent ? 'current-class' : ''}`;
            
            classCard.innerHTML = `
                <div class="class-header">
                    <span class="class-time">${this.formatTime12Hour(startTime)} - ${this.formatTime12Hour(endTime)}</span>
                    <span class="class-duration">${duration} min</span>
                    ${isCurrent ? '<span class="current-badge">Now</span>' : ''}
                </div>
                <h4 class="class-title">${classInfo.course}</h4>
                <div class="class-instructor"><i class="fas fa-chalkboard-teacher"></i> ${classInfo.instructor}</div>
                <div class="class-students"><i class="fas fa-users"></i> ${this.truncateText(classInfo.students, 30)}</div>
                
                <div class="class-details">
                    <div class="detail-row"><strong>Course:</strong> ${classInfo.course}</div>
                    <div class="detail-row"><strong>Instructor:</strong> ${classInfo.instructor}</div>
                    <div class="detail-row"><strong>Time:</strong> ${this.formatTime12Hour(startTime)} - ${this.formatTime12Hour(endTime)} (${duration} min)</div>
                    <div class="detail-row"><strong>Students:</strong> ${classInfo.students}</div>
                </div>
                
                <button class="toggle-details-btn">Show Details <i class="fas fa-chevron-down"></i></button>
            `;

            container.appendChild(classCard);
        });
    }

    // Enhanced renderWeekView method - FIXED VERSION
    renderWeekView() {
        const grid = document.getElementById('scheduleGrid');
        if (!grid) return;
        
        grid.innerHTML = '';

        // Create time column
        const timeColumn = document.createElement('div');
        timeColumn.className = 'time-column';

        // Time header
        const timeHeader = document.createElement('div');
        timeHeader.className = 'time-slot time-header';
        timeHeader.textContent = 'Time';
        timeColumn.appendChild(timeHeader);

        // Time slots
        this.timeSlots.forEach(time => {
            const timeSlot = document.createElement('div');
            timeSlot.className = 'time-slot';
            timeSlot.textContent = this.formatTime12Hour(time);
            timeColumn.appendChild(timeSlot);
        });

        grid.appendChild(timeColumn);

        // Create day columns
        const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const startOfWeek = new Date(this.currentWeek);
        startOfWeek.setDate(this.currentWeek.getDate() - this.currentWeek.getDay());

        daysOfWeek.forEach((day, index) => {
            const dayColumn = document.createElement('div');
            dayColumn.className = 'day-column';
            dayColumn.setAttribute('data-day', day);

            const currentDate = new Date(startOfWeek);
            currentDate.setDate(startOfWeek.getDate() + index);

            // Check if this is today
            const isToday = currentDate.toDateString() === new Date().toDateString();

            // Day header
            const dayHeader = document.createElement('div');
            dayHeader.className = `day-header ${isToday ? 'today' : ''}`;
            dayHeader.innerHTML = `
                <div class="day-name">${day.substring(0, 3)}</div>
                <div class="day-date">${currentDate.getDate()}</div>
            `;
            dayColumn.appendChild(dayHeader);

            // Track classes that span multiple slots
            const processedClasses = new Set();

            // Time slots for this day
            this.timeSlots.forEach((time, timeIndex) => {
                const timeSlot = document.createElement('div');
                timeSlot.className = 'time-grid-slot';
                timeSlot.setAttribute('data-time', time);
                
                // Find classes for this day and time
                const dayClasses = this.getClassesForDayAndTime(day, time);
                const availableClasses = dayClasses.filter(cls => 
                    !processedClasses.has(`${cls.course}-${cls.startTime}-${cls.endTime}`)
                );
                
                // Add class count indicator for styling
                if (availableClasses.length > 1) {
                    if (availableClasses.length === 2) {
                        timeSlot.classList.add('multiple-classes');
                    } else if (availableClasses.length >= 3) {
                        timeSlot.classList.add('triple-classes');
                    }
                }
                
                if (availableClasses.length > 0) {
                    availableClasses.forEach(classInfo => {
                        const classId = `${classInfo.course}-${classInfo.startTime}-${classInfo.endTime}`;
                        
                        if (!processedClasses.has(classId)) {
                            const classBlock = this.createClassBlock(classInfo, availableClasses.length);
                            timeSlot.appendChild(classBlock);
                            
                            // Mark long duration classes as processed
                            const duration = this.getClassDuration(classInfo.startTime, classInfo.endTime);
                            if (duration > 30) {
                                processedClasses.add(classId);
                            }
                        }
                    });
                }

                dayColumn.appendChild(timeSlot);
            });

            grid.appendChild(dayColumn);
        });

        // Add current time indicator after rendering
        setTimeout(() => this.showCurrentTimeIndicator(), 100);
    }

    getClassesForDayAndTime(day, time) {
        return this.classes.filter(classInfo => {
            if (classInfo.day !== day) return false;
            
            const classStart = this.timeToMinutes(classInfo.startTime);
            const classEnd = this.timeToMinutes(classInfo.endTime);
            const slotTime = this.timeToMinutes(time);
            
            return slotTime >= classStart && slotTime < classEnd;
        });
    }

    // Enhanced createClassBlock method - FIXED VERSION
    createClassBlock(classInfo, classCount = 1) {
        const block = document.createElement('div');
        block.className = `class-block ${this.getCourseType(classInfo.course)}`;
        
        const duration = this.getClassDuration(classInfo.startTime, classInfo.endTime);
        
        // Calculate height based on duration (each 30-minute slot = 50px height)
        const slotsSpanned = Math.ceil(duration / 30);
        const height = Math.max(40, (slotsSpanned * 50) - 4); // Minimum 40px height
        
        // Handle long duration classes differently
        if (slotsSpanned > 1) {
            block.classList.add('long-duration');
            block.style.height = `${height}px`;
            block.style.position = 'absolute';
            block.style.top = '2px';
            block.style.left = '2px';
            block.style.right = '2px';
            block.style.zIndex = '5';
        } else {
            block.style.height = `${height}px`;
        }
        
        // Adjust for multiple classes in the same time slot
        if (classCount > 1 && slotsSpanned === 1) {
            if (classCount === 2) {
                block.style.width = 'calc(50% - 2px)';
            } else if (classCount === 3) {
                block.style.width = 'calc(33.33% - 2px)';
            } else {
                block.style.width = `calc(${100/classCount}% - 2px)`;
            }
        }
        
        // Create content with proper truncation
        const courseTitle = this.truncateText(classInfo.course, classCount > 1 ? 12 : 18);
        const instructorName = this.truncateText(classInfo.instructor, classCount > 1 ? 15 : 25);
        const studentCount = this.getStudentCount(classInfo.students);
        
        block.innerHTML = `
            <div class="class-title" title="${classInfo.course}">${courseTitle}</div>
            <div class="class-instructor" title="${classInfo.instructor}">
                <i class="fas fa-user"></i>
                ${instructorName}
            </div>
            <div class="class-students" title="${classInfo.students}">
                <i class="fas fa-users"></i>
                ${studentCount} student${studentCount !== 1 ? 's' : ''}
            </div>
        `;

        // Add tooltip functionality
        block.addEventListener('mouseenter', (e) => this.showTooltip(e, classInfo));
        block.addEventListener('mouseleave', () => this.hideTooltip());
        
        // Add click functionality for mobile
        block.addEventListener('click', (e) => {
            e.stopPropagation();
            this.showTooltip(e, classInfo);
            setTimeout(() => this.hideTooltip(), 3000); // Auto-hide after 3 seconds
        });

        return block;
    }

    getCourseType(courseName) {
        const course = courseName.toLowerCase();
        if (course.includes('math')) return 'math';
        if (course.includes('science')) return 'science';
        if (course.includes('english')) return 'english';
        if (course.includes('history')) return 'history';
        if (course.includes('art')) return 'art';
        return 'default';
    }

    getStudentCount(studentsString) {
        return studentsString.split(',').length;
    }

    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }

    // Enhanced tooltip positioning
    showTooltip(event, classInfo) {
        const tooltip = document.getElementById('classTooltip');
        if (!tooltip) return;
        
        const duration = this.getClassDuration(classInfo.startTime, classInfo.endTime);
        const studentCount = this.getStudentCount(classInfo.students);
        
        tooltip.innerHTML = `
            <div class="tooltip-title">${classInfo.course}</div>
            <div class="tooltip-info">
                <i class="fas fa-clock"></i> 
                ${this.formatTime12Hour(classInfo.startTime)} - ${this.formatTime12Hour(classInfo.endTime)} 
                <span style="color: #4a6cf7; font-weight: 600;">(${duration} min)</span>
            </div>
            <div class="tooltip-info">
                <i class="fas fa-chalkboard-teacher"></i> 
                ${classInfo.instructor}
            </div>
            <div class="tooltip-info">
                <i class="fas fa-users"></i> 
                ${studentCount} student${studentCount !== 1 ? 's' : ''}
            </div>
            <div class="tooltip-info">
                <i class="fas fa-calendar-day"></i> 
                ${classInfo.day}
            </div>
        `;

        const rect = event.target.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        // Smart positioning to avoid going off-screen
        let left = rect.right + 10;
        let top = rect.top;
        
        // Adjust if tooltip would go off right edge
        if (left + 300 > window.innerWidth) {
            left = rect.left - 310;
        }
        
        // Adjust if tooltip would go off bottom edge
        if (top + tooltipRect.height > window.innerHeight) {
            top = window.innerHeight - tooltipRect.height - 10;
        }
        
        // Adjust if tooltip would go off top edge
        if (top < 10) {
            top = 10;
        }
        
        tooltip.style.left = `${left}px`;
        tooltip.style.top = `${top}px`;
        tooltip.classList.add('show');
    }

    hideTooltip() {
        const tooltip = document.getElementById('classTooltip');
        if (tooltip) {
            tooltip.classList.remove('show');
        }
    }

    // Click outside to hide tooltip
    

    getClassDuration(startTime, endTime) {
        const start = this.timeToMinutes(startTime);
        const end = this.timeToMinutes(endTime);
        return end - start;
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

    showCurrentTimeIndicator() {
        // Remove existing indicators
        document.querySelectorAll('.current-time-indicator').forEach(el => el.remove());

        const now = new Date();
        const currentTime = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
        const currentMinutes = this.timeToMinutes(currentTime);
        
        // Only show if within schedule hours and in week view
        if (this.currentView === 'week' && 
            currentMinutes >= this.timeToMinutes('08:00') && 
            currentMinutes <= this.timeToMinutes('23:00')) { // Changed from 17:00 to 23:00
            
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
}

// Main roster functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the schedule calendar
    window.scheduleCalendar = new ScheduleCalendar();
    
    // Update current time display every minute
    function updateCurrentTime() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        const timeDisplay = document.getElementById('currentDateTime');
        if (timeDisplay) {
            timeDisplay.textContent = now.toLocaleDateString('en-US', options);
        }
    }
    
    setInterval(updateCurrentTime, 60000);
    updateCurrentTime();
    
    // Update time indicator every minute
    setInterval(() => {
        if (window.scheduleCalendar) {
            window.scheduleCalendar.showCurrentTimeIndicator();
        }
    }, 60000);
});