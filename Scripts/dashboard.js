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
                
                // Add event indicator
                const eventIndicator = document.createElement('div');
                eventIndicator.className = 'event-indicator';
                eventIndicator.innerHTML = `
                    <i class="fas fa-circle"></i>
                    <span class="event-count">${dayEvents.length}</span>
                `;
                dayElement.appendChild(eventIndicator);
                
                // Add click listener to show student list
                dayElement.addEventListener('click', (e) => this.showStudentList(e, dateString, dayEvents));
                dayElement.style.cursor = 'pointer';
                dayElement.classList.add('has-events');
            }

            calendarDays.appendChild(dayElement);
        }
    }

    showStudentList(e, date, events) {
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

        // Group events by student/class
        const studentClasses = this.groupEventsByStudent(events);
        
        // Determine how many to show initially
        const maxInitialDisplay = 5;
        const hasMore = studentClasses.length > maxInitialDisplay;
        const initialStudents = studentClasses.slice(0, maxInitialDisplay);
        const remainingStudents = studentClasses.slice(maxInitialDisplay);

        popover.innerHTML = `
            <div class="student-popover-header">
                <h3><i class="fas fa-calendar-day"></i> ${displayDate}</h3>
                <button class="close-popover">×</button>
            </div>
            <div class="student-popover-content">
                <div class="student-count-info">
                    <i class="fas fa-users"></i>
                    <span>${studentClasses.length} class${studentClasses.length !== 1 ? 'es' : ''} scheduled</span>
                </div>
                <div class="student-list" id="studentList">
                    ${this.renderStudentList(initialStudents)}
                </div>
                ${hasMore ? `
                    <div class="view-more-section">
                        <button class="view-more-btn" id="viewMoreBtn">
                            <i class="fas fa-plus"></i> View ${remainingStudents.length} more
                        </button>
                    </div>
                ` : ''}
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
        
        if (hasMore) {
            const viewMoreBtn = popover.querySelector('#viewMoreBtn');
            let isExpanded = false;
            
            viewMoreBtn.addEventListener('click', () => {
                const studentList = popover.querySelector('#studentList');
                
                if (!isExpanded) {
                    // Expand to show all students
                    studentList.innerHTML = this.renderStudentList(studentClasses);
                    viewMoreBtn.innerHTML = '<i class="fas fa-minus"></i> Show less';
                    isExpanded = true;
                } else {
                    // Collapse to show only initial students
                    studentList.innerHTML = this.renderStudentList(initialStudents);
                    viewMoreBtn.innerHTML = `<i class="fas fa-plus"></i> View ${remainingStudents.length} more`;
                    isExpanded = false;
                }
            });
        }

        // Add click outside to close
        setTimeout(() => {
            document.addEventListener('click', this.handleOutsideClick.bind(this), { once: true });
        }, 0);

        this.currentPopover = popover;
    }

    groupEventsByStudent(events) {
        // Group events by course/student combination
        const grouped = {};
        
        events.forEach(event => {
            // Parse students from the event (assuming format: "Student1, Student2, ...")
            const students = event.students ? event.students.split(', ') : ['Unknown Student'];
            
            students.forEach(student => {
                const key = `${student}_${event.title}`;
                if (!grouped[key]) {
                    grouped[key] = {
                        student: student.trim(),
                        course: event.title.split(' - ')[0], // Extract course name
                        instructor: event.title.split(' - ')[1] || 'Unknown Instructor',
                        time: event.time,
                        duration: event.duration,
                        description: event.description
                    };
                }
            });
        });
        
        return Object.values(grouped);
    }

    renderStudentList(studentClasses) {
        return studentClasses.map(studentClass => `
            <div class="student-item">
                <div class="student-info">
                    <div class="student-name">
                        <i class="fas fa-user"></i>
                        <strong>${studentClass.student}</strong>
                    </div>
                    <div class="class-details">
                        <div class="course-info">
                            <i class="fas fa-book"></i>
                            <span>${studentClass.course}</span>
                        </div>
                        <div class="time-info">
                            <i class="fas fa-clock"></i>
                            <span>${studentClass.time} (${studentClass.duration})</span>
                        </div>
                        <div class="instructor-info">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>${studentClass.instructor}</span>
                        </div>
                    </div>
                </div>
                <div class="student-actions">
                    <button class="view-student-btn" onclick="viewStudentDetails('${studentClass.student}')">
                        <i class="fas fa-eye"></i> View Student
                    </button>
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

// Function to handle viewing student details (to be implemented based on your needs)
function viewStudentDetails(studentName) {
    // This function should be implemented based on your application's needs
    // For example, redirect to student profile page or show student details modal
    console.log('Viewing details for student:', studentName);
    
    // Example implementation - you can modify this based on your requirements
    alert(`Feature to view details for ${studentName} will be implemented here.\n\nThis could:\n- Open a student profile modal\n- Navigate to student details page\n- Show additional student information`);
    
    // Example: Redirect to student profile page
    // window.location.href = `studentProfile.php?student=${encodeURIComponent(studentName)}`;
    
    // Example: Show modal with student details
    // showStudentDetailsModal(studentName);
}

// Initialize calendar when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const calendar = new Calendar();
});