<?php
// Include the database settings
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Initialize variables to avoid warnings
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : null;
$error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure that user_type is set and valid
    if (!$user_type) {
        $error = "Please select a user type before submitting.";
    } else {
        // Start a transaction to ensure atomicity
        $conn->begin_transaction();

        try {
            if ($user_type === 'student') {
                // Student form submission
                $last_name = $conn->real_escape_string($_POST['Last_Name']);
                $first_name = $conn->real_escape_string($_POST['First_Name']);
                $gender = $conn->real_escape_string($_POST['Gender']);
                $dob = $conn->real_escape_string($_POST['DOB']);
                $school_syllabus = $conn->real_escape_string($_POST['School_Syllabus']);
                $school_intake = $conn->real_escape_string($_POST['School_Intake']);
                $current_grade = $conn->real_escape_string($_POST['Current_School_Grade']);
                $school = $conn->real_escape_string($_POST['School']);
                $mathology_level = $conn->real_escape_string($_POST['Mathology_Level']);
                $email = $conn->real_escape_string($_POST['email']);
                $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_BCRYPT);
                $course_id = intval($_POST['course_id']);
                $enrollment_date = $conn->real_escape_string($_POST['Enrollment_Date']);
                $day = $conn->real_escape_string($_POST['Day']);
                $start_time = $conn->real_escape_string($_POST['Start_Time']);
                $end_time = $conn->real_escape_string($_POST['End_Time']);
                $how_did_you_heard_about_us = $conn->real_escape_string($_POST['How_Did_You_Heard_About_Us']);

                // Primary contact
                $primary_owner_last_name = $conn->real_escape_string($_POST['primary_owner_last_name']);
                $primary_owner_first_name = $conn->real_escape_string($_POST['primary_owner_first_name']);
                $primary_relationship = $conn->real_escape_string($_POST['primary_relationship']);
                $primary_phone = $conn->real_escape_string($_POST['primary_phone']);
                $primary_email = $conn->real_escape_string($_POST['primary_email']);
                $primary_address = $conn->real_escape_string($_POST['primary_address']);
                $primary_postcode = $conn->real_escape_string($_POST['primary_postcode']);

                // Insert into students table
                $insertStudentQuery = "INSERT INTO students (Last_Name, First_Name, Gender, DOB, School_Syllabus, School_Intake, Current_School_Grade, School, Mathology_Level, How_Did_You_Heard_About_Us)
                                       VALUES ('$last_name', '$first_name', '$gender', '$dob', '$school_syllabus', '$school_intake', '$current_grade', '$school', '$mathology_level', '$how_did_you_heard_about_us')";
                if (!$conn->query($insertStudentQuery)) {
                    throw new Exception("Error adding student: " . $conn->error);
                }

                // Get the last inserted student ID
                $student_id = $conn->insert_id;

                // Insert into users table
                $insertUserQuery = "INSERT INTO users (email, password, role, student_id)
                                    VALUES ('$email', '$password', 'student', '$student_id')";
                if (!$conn->query($insertUserQuery)) {
                    throw new Exception("Error adding user: " . $conn->error);
                }

                // Insert into student_courses table
                $insertStudentCourseQuery = "INSERT INTO student_courses (student_id, course_id, enrollment_date)
                                             VALUES ('$student_id', '$course_id', '$enrollment_date')";
                if (!$conn->query($insertStudentCourseQuery)) {
                    throw new Exception("Error adding student course: " . $conn->error);
                }
                $student_course_id = $conn->insert_id;

                // Insert into student_timetable table
                $insertTimetableQuery = "INSERT INTO student_timetable (student_course_id, day, start_time, end_time, status)
                                         VALUES ('$student_course_id', '$day', '$start_time', '$end_time', 'active')";
                if (!$conn->query($insertTimetableQuery)) {
                    throw new Exception("Error adding student timetable: " . $conn->error);
                }

                $insertAttendanceQuery = "INSERT INTO attendance_records (student_id)
                                         VALUES ('$student_id')";
                if (!$conn->query($insertAttendanceQuery)) {
                    throw new Exception("Error adding into attendance table: " . $conn->error);
                }

                $insertPaymentQuery = "INSERT INTO payment (student_id)
                                         VALUES ('$student_id')";
                if (!$conn->query($insertPaymentQuery)) {
                    throw new Exception("Error adding into payment table: " . $conn->error);
                }

                $insertPrimaryContactQuery = "INSERT INTO primary_contact_number 
    (student_id, Last_Name, First_Name, Relationship_with_Student, phone, email, address, postcode)
    VALUES ('$student_id', '$primary_owner_last_name', '$primary_owner_first_name', '$primary_relationship', '$primary_phone', '$primary_email', '$primary_address','$primary_postcode')";
                if (!$conn->query($insertPrimaryContactQuery)) {
                    throw new Exception("Error adding primary contact: " . $conn->error);
                }

                // Secondary contact (optional)
                if (
                    !empty($_POST['secondary_owner_last_name']) ||
                    !empty($_POST['secondary_owner_first_name']) ||
                    !empty($_POST['secondary_relationship']) ||
                    !empty($_POST['secondary_phone'])
                ) {
                    $secondary_owner_last_name = $conn->real_escape_string($_POST['secondary_owner_last_name']);
                    $secondary_owner_first_name = $conn->real_escape_string($_POST['secondary_owner_first_name']);
                    $secondary_relationship = $conn->real_escape_string($_POST['secondary_relationship']);
                    $secondary_phone = $conn->real_escape_string($_POST['secondary_phone']);

                    $insertSecondaryContactQuery = "INSERT INTO secondary_contact_number 
        (student_id, Last_Name, First_Name, Relationship_with_Student, phone)
        VALUES ('$student_id', '$secondary_owner_last_name', '$secondary_owner_first_name', '$secondary_relationship', '$secondary_phone')";
                    if (!$conn->query($insertSecondaryContactQuery)) {
                        throw new Exception("Error adding secondary contact: " . $conn->error);
                    }
                }

                // Commit the transaction
                $conn->commit();
                header("Location: ../Pages/admin/users.php?active_tab=students&message=Student+and+associated+user+added+successfully");
                exit();

            } elseif ($user_type === 'instructor') {
                // Instructor form submission
                $last_name = $conn->real_escape_string($_POST['Last_Name']);
                $first_name = $conn->real_escape_string($_POST['First_Name']);
                $gender = $conn->real_escape_string($_POST['Gender']);
                $dob = $conn->real_escape_string($_POST['DOB']);
                $highest_education = $conn->real_escape_string($_POST['Highest_Education']);
                $remark = $conn->real_escape_string($_POST['Remark']);
                $training_status = $conn->real_escape_string($_POST['Training_Status']);
                $employment_type = $conn->real_escape_string($_POST['Employment_Type']);
                $working_days = isset($_POST['Working_Days']) ? $conn->real_escape_string(implode(',', (array) $_POST['Working_Days'])) : null;
                $email = $conn->real_escape_string($_POST['email']);
                $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_BCRYPT);
                $contact = $conn->real_escape_string($_POST['contact']);
                $hiring_status = $conn->real_escape_string($_POST['hiring_status']);
                $total_hours = floatval($_POST['Total_Hours']);

                $course_level = $conn->real_escape_string($_POST['course_level']);
                $course_id = intval($_POST['course_id']);

                $working_days = null;
                if ($employment_type === 'Part-Time' && isset($_POST['Working_Days'])) {
                    $working_days_arr = array_map([$conn, 'real_escape_string'], (array) $_POST['Working_Days']);
                    $working_days = implode(',', $working_days_arr); // Save as comma separated string
                }

                // Insert into instructor table
                $insertInstructorQuery = "INSERT INTO instructor (
            Last_Name, First_Name, Gender, DOB, Highest_Education, Remark, Training_Status,
            Employment_Type, Working_Days, Worked_Days, contact, hiring_status, Total_Hours
        ) VALUES (
            '$last_name', '$first_name', '$gender', '$dob', '$highest_education', '$remark', '$training_status',
            '$employment_type', " . ($working_days ? "'$working_days'" : "NULL") . ", 0, '$contact', '$hiring_status', $total_hours
        )";
                if (!$conn->query($insertInstructorQuery)) {
                    throw new Exception("Error adding instructor: " . $conn->error);
                }
                $instructor_id = $conn->insert_id;

                // Insert into users table
                $insertUserQuery = "INSERT INTO users (email, password, role, instructor_id)
                            VALUES ('$email', '$password', 'instructor', '$instructor_id')";
                if (!$conn->query($insertUserQuery)) {
                    throw new Exception("Error adding user: " . $conn->error);
                }

                // Insert instructor_courses (associate instructor with course)
                $assigned_date = date('Y-m-d');
                $insertInstructorCourseQuery = "INSERT INTO instructor_courses (instructor_id, course_id, assigned_date)
                                        VALUES ('$instructor_id', '$course_id', '$assigned_date')";
                if (!$conn->query($insertInstructorCourseQuery)) {
                    throw new Exception("Error adding instructor course: " . $conn->error);
                }
                $instructor_course_id = $conn->insert_id;

                // Insert instructor_timetable
                if ($employment_type === 'Part-Time' && isset($_POST['Working_Days'])) {
                    foreach ($_POST['Working_Days'] as $day) {
                        $day = $conn->real_escape_string($day);
                        $start_time = isset($_POST['start_time'][$day]) ? $conn->real_escape_string($_POST['start_time'][$day]) : '00:00:00';
                        $end_time = isset($_POST['end_time'][$day]) ? $conn->real_escape_string($_POST['end_time'][$day]) : '00:00:00';
                        $insertTimetableQuery = "INSERT INTO instructor_timetable (day, start_time, end_time, status, instructor_course_id, course)
                                         VALUES ('$day', '$start_time', '$end_time', 'active', '$instructor_course_id', '')";
                        if (!$conn->query($insertTimetableQuery)) {
                            throw new Exception("Error adding instructor timetable: " . $conn->error);
                        }
                    }
                } else {
                    // For Full-Time, insert a blank timetable entry (or a default if needed)
                    $insertTimetableQuery = "INSERT INTO instructor_timetable (day, start_time, end_time, status, instructor_course_id, course)
                                     VALUES ('', '00:00:00', '00:00:00', 'active', '$instructor_course_id', '')";
                    if (!$conn->query($insertTimetableQuery)) {
                        throw new Exception("Error adding blank instructor timetable: " . $conn->error);
                    }
                }

                // Insert a blank attendance record 
                $insertAttendanceQuery = "INSERT INTO attendance_records (instructor_id, course)
                                 VALUES ('$instructor_id', '$course_id')";
                if (!$conn->query($insertAttendanceQuery)) {
                    throw new Exception("Error adding into attendance table: " . $conn->error);
                }

                // Commit the transaction
                $conn->commit();
                header("Location: ../Pages/admin/users.php?active_tab=instructors&message=Instructor+and+associated+user+added+successfully");
                exit();
            } elseif ($user_type === 'admin') {
                $email = $conn->real_escape_string($_POST['email']);
                $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_BCRYPT);
                $insertUserQuery = "INSERT INTO users (email, password, role)
                                    VALUES ('$email', '$password', 'admin')";
                if (!$conn->query($insertUserQuery)) {
                    throw new Exception("Error adding user: " . $conn->error);
                }
                  $conn->commit();
                header("Location: ../Pages/admin/users.php?active_tab=admins&message=Admin+added+successfully");
                exit();
            }
        } catch (Exception $e) {
            // Rollback the transaction on error
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/forms.css">
    <title>Add Entry</title>
</head>

<body>
    <h1>Add New Entry</h1>
    <?php if ($error) {
        echo "<p class='error'>$error</p>";
    } ?>

    <form id="user-type-form" method="POST">
        <label for="user_type">User Type:</label>
        <select id="user_type" name="user_type" required onchange="toggleForms()">
            <option value="">Select User Type</option>
            <option value="student">Student</option>
            <option value="instructor">Instructor</option>
            <option value="admin">Admin</option>
        </select><br><br>
    </form>

    <!-- Student Form -->
    <form id="student-form" method="POST" style="display: none;">
        <input type="hidden" name="user_type" value="student">
        <h2>Student Details</h2>
        <label for="student_last_name">Last Name:</label>
        <input type="text" id="student_last_name" name="Last_Name" required><br>
        <label for="student_first_name">First Name:</label>
        <input type="text" id="student_first_name" name="First_Name" required><br>
        <label for="student_gender">Gender:</label>
        <select id="student_gender" name="Gender" required>
            <option value="1">Male</option>
            <option value="0">Female</option>
        </select><br>
        <label for="student_dob">Date of Birth:</label>
        <input type="date" id="student_dob" name="DOB" required><br>
        <label for="student_school_syllabus">School Syllabus:</label>
        <input type="text" id="student_school_syllabus" name="School_Syllabus"><br>
        <label for="student_school_intake">School Intake (Month):</label>
        <select id="student_school_intake" name="School_Intake" required>
            <option value="">Select Month</option>
            <option value="January">January</option>
            <option value="February">February</option>
            <option value="March">March</option>
            <option value="April">April</option>
            <option value="May">May</option>
            <option value="June">June</option>
            <option value="July">July</option>
            <option value="August">August</option>
            <option value="September">September</option>
            <option value="October">October</option>
            <option value="November">November</option>
            <option value="December">December</option>
        </select><br>
        <label for="student_current_grade">Current School Grade:</label>
        <input type="text" id="student_current_grade" name="Current_School_Grade"><br>
        <label for="student_school">School:</label>
        <input type="text" id="student_school" name="School"><br>
        <label for="student_mathology_level">Mathology Level:</label>
        <select id="student_mathology_level" name="Mathology_Level" required>
            <option value="">Select Level</option>
            <?php
            for ($i = 1; $i <= 9; $i++) {
                echo "<option value='$i'>Level $i</option>";
            }
            ?>
        </select><br>
        <label for="student_email">Email:</label>
        <input type="email" id="student_email" name="email" required><br>
        <label for="student_password">Password:</label>
        <input type="password" id="student_password" name="password" required><br><br>
        <label for="course_level_select">Course Level:</label>
        <select id="course_level_select" name="course_level" required onchange="filterCoursesByLevel()">
            <option value="">Select Level</option>
            <?php
            // Get all unique course levels
            $levels = [];
            $courses_result = $conn->query("SELECT DISTINCT level FROM courses WHERE level IS NOT NULL");
            while ($row = $courses_result->fetch_assoc()) {
                $level = htmlspecialchars($row['level'], ENT_QUOTES);
                echo "<option value=\"$level\">$level</option>";
            }
            ?>
        </select><br>
        <label for="student_course">Course:</label>
        <select id="student_course" name="course_id" required>
            <option value="">Select Level</option>
        </select><br>
        <label for="enrollment_date">Enrollment Date:</label>
        <input type="date" id="enrollment_date" name="Enrollment_Date" required><br>
        <label for="student_day">Day:</label>
        <select id="student_day" name="Day" required>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
            <option value="Sunday">Sunday</option>
        </select><br>
        <label for="student_start_time">Start Time:</label>
        <input type="time" id="student_start_time" name="Start_Time" required><br>
        <label for="student_end_time">End Time:</label>
        <input type="time" id="student_end_time" name="End_Time" required><br><br>

        <fieldset style="margin-top:16px;">
            <legend>Primary Contact</legend>
            <label for="primary_owner_last_name">Last Name:</label>
            <input type="text" id="primary_owner_last_name" name="primary_owner_last_name" required><br>
            <label for="primary_owner_first_name">First Name:</label>
            <input type="text" id="primary_owner_first_name" name="primary_owner_first_name" required><br>
            <label for="primary_relationship">Relationship with Student:</label>
            <input type="text" id="primary_relationship" name="primary_relationship" required><br>
            <label for="primary_phone">Phone:</label>
            <input type="text" id="primary_phone" name="primary_phone" required><br>
            <label for="primary_email">Email:</label>
            <input type="text" id="primary_email" name="primary_email" required><br>
            <label for="primary_address">Address:</label>
            <input type="text" id="primary_address" name="primary_address" required><br>
            <label for="primary_postcode">Postcode:</label>
            <input type="text" id="primary_postcode" name="primary_postcode" required><br>
        </fieldset>
        <fieldset style="margin-top:10px;">
            <legend>Secondary Contact</legend>
            <label for="secondary_owner_last_name">Last Name:</label>
            <input type="text" id="secondary_owner_last_name" name="secondary_owner_last_name"><br>
            <label for="secondary_owner_first_name">First Name:</label>
            <input type="text" id="secondary_owner_first_name" name="secondary_owner_first_name"><br>
            <label for="secondary_relationship">Relationship with Student:</label>
            <input type="text" id="secondary_relationship" name="secondary_relationship"><br>
            <label for="secondary_phone">Phone:</label>
            <input type="text" id="secondary_phone" name="secondary_phone"><br>
        </fieldset>
        <label for="How_Did_You_Heard_About_Us">How did you hear about us?</label>
        <input type="text" id="How_Did_You_Heard_About_Us" name="How_Did_You_Heard_About_Us" maxlength="100"><br>
        <button type="submit">Add Student</button>
        <a href="../Pages/admin/users.php">Cancel</a>
    </form>

    <!-- Instructor Form -->
    <form id="instructor-form" method="POST" style="display: none;">
        <input type="hidden" name="user_type" value="instructor">
        <h2>Instructor Details</h2>
        <label for="instructor_last_name">Last Name:</label>
        <input type="text" id="instructor_last_name" name="Last_Name" required><br>
        <label for="instructor_first_name">First Name:</label>
        <input type="text" id="instructor_first_name" name="First_Name" required><br>
        <label for="instructor_gender">Gender:</label>
        <select id="instructor_gender" name="Gender" required>
            <option value="1">Male</option>
            <option value="0">Female</option>
        </select><br>
        <label for="instructor_dob">Date of Birth:</label>
        <input type="date" id="instructor_dob" name="DOB" required><br>
        <label for="instructor_highest_education">Highest Education:</label>
        <input type="text" id="instructor_highest_education" name="Highest_Education"><br>
        <label for="instructor_remark">Remark:</label>
        <textarea id="instructor_remark" name="Remark"></textarea><br>
        <label for="instructor_training_status">Training Status:</label>
        <input type="text" id="instructor_training_status" name="Training_Status"><br>
        <label for="instructor_contact">Contact Number:</label>
        <input type="text" id="instructor_contact" name="contact" pattern="[0-9]{10,12}" maxlength="12" required><br>
        <label for="instructor_hiring_status">Hiring Status:</label>
        <select id="instructor_hiring_status" name="hiring_status" required>
            <option value="true">Hired</option>
            <option value="false">Not Hired</option>
        </select><br>
        <label for="instructor_total_hours">Total Hours:</label>
        <input type="number" id="instructor_total_hours" name="Total_Hours" step="0.1" min="0" required><br>
        <label for="instructor_employment_type">Employment Type:</label>
        <select id="instructor_employment_type" name="Employment_Type" required>
            <option value="Full-Time">Full-Time</option>
            <option value="Part-Time">Part-Time</option>
        </select><br>

        <!-- Course Level and Name selection -->
        <label for="instructor_course_level">Course Level:</label>
        <select id="instructor_course_level" name="course_level" required>
            <option value="">Select Level</option>
            <?php
            // Get enum values for level from courses table
            $levelEnumRes = $conn->query("SHOW COLUMNS FROM courses LIKE 'level'");
            $levelRow = $levelEnumRes->fetch_assoc();
            preg_match("/^enum\((.*)\)$/", $levelRow['Type'], $matches);
            $levels = [];
            if (isset($matches[1])) {
                foreach (explode(",", $matches[1]) as $level) {
                    $val = trim($level, "'");
                    $levels[] = $val;
                    echo "<option value=\"$val\">$val</option>";
                }
            }
            ?>
        </select><br>
        <label for="instructor_course_name">Course Name:</label>
        <select id="instructor_course_name" name="course_id" required>
            <option value="">Select Course</option>
            <!-- Options will be dynamically populated by JS -->
        </select><br>
        <div id="part-time-days-times" style="display:none;">
            <label for="instructor_working_days">Working Days:</label>
            <select id="instructor_working_days" name="Working_Days[]" multiple>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
            </select>
            <div id="day-times-container"></div>
        </div>
        <br>
        <label for="instructor_email">Email:</label>
        <input type="email" id="instructor_email" name="email" required><br>
        <label for="instructor_password">Password:</label>
        <input type="password" id="instructor_password" name="password" required><br>
        <button type="submit">Add Instructor</button>
        <a href="../Pages/admin/users.php">Cancel</a>
    </form>

    <!-- Admin Form -->
    <form id="admin-form" method="POST" style="display: none;">
        <input type="hidden" name="user_type" value="admin">
        <h2>Admin Details</h2>
        <label for="admin_email">Email:</label>
        <input type="email" id="admin_email" name="email" required><br>
        <label for="admin_password">Password:</label>
        <input type="password" id="admin_password" name="password" required><br><br>
        <button type="submit">Add Admin</button>
        <a href="../Pages/admin/users.php">Cancel</a>
    </form>

    <script>
        const allCourses = <?php
        $courses = [];
        $res = $conn->query("SELECT course_id, course_name, level FROM courses");
        while ($row = $res->fetch_assoc()) {
            $courses[] = $row;
        }
        echo json_encode($courses);
        ?>;

        document.getElementById('course_level_select').addEventListener('change', function () {
            const selectedLevel = this.value;
            const nameSelect = document.getElementById('student_course');
            nameSelect.innerHTML = '<option value="">Select Course</option>';
            allCourses.forEach(function (course) {
                if (course.level === selectedLevel) {
                    const opt = document.createElement('option');
                    opt.value = course.course_id;
                    opt.textContent = course.course_name;
                    nameSelect.appendChild(opt);
                }
            });
        });

        document.getElementById('instructor_course_level').addEventListener('change', function () {
            const selectedLevel = this.value;
            const nameSelect = document.getElementById('instructor_course_name');
            nameSelect.innerHTML = '<option value="">Select Course</option>';
            allCourses.forEach(function (course) {
                if (course.level === selectedLevel) {
                    const opt = document.createElement('option');
                    opt.value = course.course_id;
                    opt.textContent = course.course_name;
                    nameSelect.appendChild(opt);
                }
            });
        });

        // --- Employment type logic: days, time ranges ---
        function setWorkingDaysState() {
            const employmentType = document.getElementById('instructor_employment_type');
            const daysDiv = document.getElementById('part-time-days-times');
            const workingDaysInput = document.getElementById('instructor_working_days');
            const dayTimesContainer = document.getElementById('day-times-container');
            if (employmentType.value === 'Part-Time') {
                daysDiv.style.display = '';
                workingDaysInput.disabled = false;
                // Show/hide time pickers for each selected day
                updateDayTimes();
            } else {
                daysDiv.style.display = 'none';
                workingDaysInput.disabled = true;
                dayTimesContainer.innerHTML = '';
                // Deselect all
                for (let i = 0; i < workingDaysInput.options.length; i++) {
                    workingDaysInput.options[i].selected = false;
                }
            }
        }
        document.getElementById('instructor_employment_type').addEventListener('change', setWorkingDaysState);

        // Show time pickers for each selected day (Part-Time only)
        function updateDayTimes() {
            const workingDaysInput = document.getElementById('instructor_working_days');
            const dayTimesContainer = document.getElementById('day-times-container');
            dayTimesContainer.innerHTML = '';
            Array.from(workingDaysInput.selectedOptions).forEach(option => {
                const day = option.value;
                const div = document.createElement('div');
                div.innerHTML = `
                <label>${day} Start:</label>
                <input type="time" name="start_time[${day}]" required>
                <label>End:</label>
                <input type="time" name="end_time[${day}]" required>
            `;
                dayTimesContainer.appendChild(div);
            });
        }
        document.getElementById('instructor_working_days').addEventListener('change', updateDayTimes);

        // On page load or form show
        document.addEventListener('DOMContentLoaded', function () {
            setWorkingDaysState();
        });

        // When switching to instructor form (if your toggleForms is used)
        function toggleForms() {
            const userType = document.getElementById('user_type').value;
            const studentForm = document.getElementById('student-form');
            const instructorForm = document.getElementById('instructor-form');
            const adminForm = document.getElementById('admin-form');
            if (userType === 'student') {
                studentForm.style.display = 'block';
                instructorForm.style.display = 'none';
                adminForm.style.display = 'none';
            } else if (userType === 'instructor') {
                studentForm.style.display = 'none';
                instructorForm.style.display = 'block';
                adminForm.style.display = 'none';
                setWorkingDaysState();
            } else if (userType === 'admin') {
                studentForm.style.display = 'none';
                instructorForm.style.display = 'none';
                adminForm.style.display = 'block';
            } else {
                studentForm.style.display = 'none';
                instructorForm.style.display = 'none';
            }
        }
    </script>
</body>

</html>