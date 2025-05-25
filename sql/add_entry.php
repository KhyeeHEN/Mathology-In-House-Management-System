<?php
// Include the database settings
include 'settings.php';

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

                // Insert into students table
                $insertStudentQuery = "INSERT INTO students (Last_Name, First_Name, Gender, DOB, School_Syllabus, Current_School_Grade, School, Mathology_Level)
                                       VALUES ('$last_name', '$first_name', '$gender', '$dob', '$school_syllabus', '$current_grade', '$school', '$mathology_level')";
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
                $email = $conn->real_escape_string($_POST['email']);
                $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_BCRYPT);
                $course_ids = $_POST['course_ids']; // Array of selected course IDs

                // Insert into instructor table
                $insertInstructorQuery = "INSERT INTO instructor (Last_Name, First_Name, Gender, DOB, Highest_Education, Remark, Training_Status)
                                          VALUES ('$last_name', '$first_name', '$gender', '$dob', '$highest_education', '$remark', '$training_status')";
                if (!$conn->query($insertInstructorQuery)) {
                    throw new Exception("Error adding instructor: " . $conn->error);
                }

                // Get the last inserted instructor ID
                $instructor_id = $conn->insert_id;

                // Insert into users table
                $insertUserQuery = "INSERT INTO users (email, password, role, instructor_id)
                                    VALUES ('$email', '$password', 'instructor', '$instructor_id')";
                if (!$conn->query($insertUserQuery)) {
                    throw new Exception("Error adding user: " . $conn->error);
                }

                // Insert selected courses into instructor_courses table
                foreach ($course_ids as $course_id) {
                    $course_id = intval($course_id); // Sanitize input
                    $insertInstructorCourseQuery = "INSERT INTO instructor_courses (instructor_id, course_id)
                                                    VALUES ('$instructor_id', '$course_id')";
                    if (!$conn->query($insertInstructorCourseQuery)) {
                        throw new Exception("Error adding instructor course: " . $conn->error);
                    }
                }

                // Commit the transaction
                $conn->commit();
                header("Location: ../Pages/admin/users.php?active_tab=instructors&message=Instructor+and+associated+user+added+successfully");
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
        <label for="student_current_grade">Current School Grade:</label>
        <input type="text" id="student_current_grade" name="Current_School_Grade"><br>
        <label for="student_school">School:</label>
        <input type="text" id="student_school" name="School"><br>
        <label for="student_mathology_level">Mathology Level:</label>
        <input type="text" id="student_mathology_level" name="Mathology_Level"><br>
        <label for="student_email">Email:</label>
        <input type="email" id="student_email" name="email" required><br>
        <label for="student_password">Password:</label>
        <input type="password" id="student_password" name="password" required><br><br>
        <label for="student_course">Course:</label>
        <select id="student_course" name="course_id" required>
            <?php
            // Fetch available courses from the database
            $courses = $conn->query("SELECT course_id, course_name FROM courses");
            while ($course = $courses->fetch_assoc()) {
                echo "<option value='{$course['course_id']}'>{$course['course_name']}</option>";
            }
            ?>
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
        <label for="instructor_email">Email:</label>
        <input type="email" id="instructor_email" name="email" required><br>
        <label for="instructor_password">Password:</label>
        <input type="password" id="instructor_password" name="password" required><br>
        <label for="instructor_courses">Courses:</label>
        <select id="instructor_courses" name="course_ids[]" multiple required>
            <?php
            // Fetch available courses from the database
            $courses = $conn->query("SELECT course_id, course_name FROM courses");
            while ($course = $courses->fetch_assoc()) {
                echo "<option value='{$course['course_id']}'>{$course['course_name']}</option>";
            }
            ?>
        </select><br><br>
        <button type="submit">Add Instructor</button>
        <a href="../Pages/admin/users.php">Cancel</a>
    </form>

    <script>
        function toggleForms() {
            const userType = document.getElementById('user_type').value;
            const studentForm = document.getElementById('student-form');
            const instructorForm = document.getElementById('instructor-form');

            if (userType === 'student') {
                studentForm.style.display = 'block';
                instructorForm.style.display = 'none';
            } else if (userType === 'instructor') {
                studentForm.style.display = 'none';
                instructorForm.style.display = 'block';
            } else {
                studentForm.style.display = 'none';
                instructorForm.style.display = 'none';
            }
        }
    </script>
</body>

</html>