// Schedule Calendar Class
class ScheduleCalendar {
    constructor() {
        this.currentWeek = new Date();
        this.timeSlots = this.generateTimeSlots();
        this.currentTooltip = null;
        
        // Convert PHP classes data to JavaScript format
        this.classes = window.rosterClasses || [];
        
        this.init();
    }

    generateTimeSlots() {
        const slots = [];
        for (let hour = 8; hour <= 17; hour++) {
            slots.push(`${hour.toString().padStart(2, '0')}:00`);
            if (hour < 17) {
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
    }

    setupEventListeners() {
        document.getElementById('prevWeek')?.addEventListener('click', () => {
            this.currentWeek.setDate(this.currentWeek.getDate() - 7);
            this.updateCurrentWeekDisplay();
            this.renderSchedule();
        });

        document.getElementById('nextWeek')?.addEventListener('click', () => {
            this.currentWeek.setDate(this.currentWeek.getDate() + 7);
            this.updateCurrentWeekDisplay();
            this.renderSchedule();
        });
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
        if (!grid) return;
        
        grid.innerHTML = '';

        // Create time column
        const timeColumn = document.createElement('div');
        timeColumn.className = 'time-column';

        // Time header
        const timeHeader = document.createElement('div');
        timeHeader.className = 'time-slot';
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

            const currentDate = new Date(startOfWeek);
            currentDate.setDate(startOfWeek.getDate() + index);

            // Day header
            const dayHeader = document.createElement('div');
            dayHeader.className = 'day-header';
            dayHeader.innerHTML = `
                <div class="day-name">${day.substring(0, 3)}</div>
                <div class="day-date">${currentDate.getDate()}</div>
            `;
            dayColumn.appendChild(dayHeader);

            // Time slots for this day
            this.timeSlots.forEach(time => {
                const timeSlot = document.createElement('div');
                timeSlot.className = 'time-grid-slot';
                
                // Find classes for this day and time
                const dayClasses = this.getClassesForDayAndTime(day, time);
                
                if (dayClasses.length > 0) {
                    dayClasses.forEach(classInfo => {
                        const classBlock = this.createClassBlock(classInfo);
                        timeSlot.appendChild(classBlock);
                    });
                }

                dayColumn.appendChild(timeSlot);
            });

            grid.appendChild(dayColumn);
        });
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

    createClassBlock(classInfo) {
        const block = document.createElement('div');
        block.className = `class-block ${classInfo.type}`;
        
        const duration = this.getClassDuration(classInfo.startTime, classInfo.endTime);
        const height = Math.max(40, (duration / 30) * 60 - 8); // Minimum 40px height
        block.style.height = `${height}px`;
        
        block.innerHTML = `
            <div class="class-title">${classInfo.course}</div>
            <div class="class-instructor">${classInfo.instructor}</div>
            <div class="class-students">${classInfo.students}</div>
        `;

        // Add tooltip functionality
        block.addEventListener('mouseenter', (e) => this.showTooltip(e, classInfo));
        block.addEventListener('mouseleave', () => this.hideTooltip());

        return block;
    }

    showTooltip(event, classInfo) {
        const tooltip = document.getElementById('classTooltip');
        if (!tooltip) return;
        
        const duration = this.getClassDuration(classInfo.startTime, classInfo.endTime);
        
        tooltip.innerHTML = `
            <div class="tooltip-title">${classInfo.course}</div>
            <div class="tooltip-info"><i class="fas fa-clock"></i> ${this.formatTime12Hour(classInfo.startTime)} - ${this.formatTime12Hour(classInfo.endTime)} (${duration} min)</div>
            <div class="tooltip-info"><i class="fas fa-chalkboard-teacher"></i> ${classInfo.instructor}</div>
            <div class="tooltip-info"><i class="fas fa-users"></i> ${classInfo.students}</div>
        `;

        const rect = event.target.getBoundingClientRect();
        tooltip.style.left = `${rect.right + 10}px`;
        tooltip.style.top = `${rect.top}px`;
        tooltip.classList.add('show');
    }

    hideTooltip() {
        const tooltip = document.getElementById('classTooltip');
        if (tooltip) {
            tooltip.classList.remove('show');
        }
    }

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
        const now = new Date();
        const currentTime = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
        const currentMinutes = this.timeToMinutes(currentTime);
        
        // Only show if within schedule hours
        if (currentMinutes >= this.timeToMinutes('08:00') && currentMinutes <= this.timeToMinutes('17:00')) {
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
    // Pass PHP classes data to JavaScript
    window.rosterClasses = <?php echo json_encode(array_map(function($class) {
        return [
            'course' => $class['course_name'],
            'instructor' => $class['instructor'],
            'students' => $class['students'],
            'day' => $class['day'],
            'startTime' => $class['start_time'],
            'endTime' => $class['end_time'],
            'type' => strtolower(preg_replace('/[^a-zA-Z]/', '', $class['course_name']))
        ];
    }, $classes)); ?>;
    
    // Toggle between day and week views
    document.getElementById('dayView')?.addEventListener('click', function() {
        document.querySelector('.current-day-roster').style.display = 'block';
        document.querySelector('.week-view-roster').style.display = 'none';
        this.classList.add('active');
        document.getElementById('weekView').classList.remove('active');
    });
    
    document.getElementById('weekView')?.addEventListener('click', function() {
        document.querySelector('.current-day-roster').style.display = 'none';
        document.querySelector('.week-view-roster').style.display = 'block';
        this.classList.add('active');
        document.getElementById('dayView').classList.remove('active');
        
        // Initialize schedule calendar if not already done
        if (!window.scheduleCalendar) {
            window.scheduleCalendar = new ScheduleCalendar();
        }
    });
    
    // Toggle class details
    document.querySelectorAll('.toggle-details-btn').forEach(button => {
        button.addEventListener('click', function() {
            const details = this.previousElementSibling;
            this.classList.toggle('active');
            details.classList.toggle('show');
            
            const icon = this.querySelector('i');
            if (details.classList.contains('show')) {
                this.innerHTML = 'Hide Details <i class="fas fa-chevron-up"></i>';
            } else {
                this.innerHTML = 'Show Details <i class="fas fa-chevron-down"></i>';
            }
        });
    });
    
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
});