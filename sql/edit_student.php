<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['id'])) {
    header("Location: ../Pages/admin/users.php?active_tab=students");
    exit();
}

$student_id = intval($_GET['id']);
$error = null;

// Fetch student data
$studentRes = $conn->query("SELECT * FROM students WHERE student_id = $student_id");
if (!$studentRes || $studentRes->num_rows === 0) {
    header("Location: ../Pages/admin/users.php?active_tab=students");
    exit();
}
$student = $studentRes->fetch_assoc();

// Fetch courses for dropdowns
$courses = [];
$courseRes = $conn->query("SELECT course_id, course_name, level FROM courses");
while ($row = $courseRes->fetch_assoc()) {
    $courses[] = $row;
}

// Get enum values for level
$levelEnumRes = $conn->query("SHOW COLUMNS FROM courses LIKE 'level'");
$levelRow = $levelEnumRes->fetch_assoc();
preg_match("/^enum\((.*)\)$/", $levelRow['Type'], $matches);
$levels = [];
if (isset($matches[1])) {
    foreach (explode(",", $matches[1]) as $level) {
        $val = trim($level, "'");
        $levels[] = $val;
    }
}

// Fetch current student course and timetable (assuming one course per student for this UI)
$stuCourseRes = $conn->query("SELECT * FROM student_courses WHERE student_id = $student_id ORDER BY enrollment_date DESC LIMIT 1");
$stuCourse = $stuCourseRes && $stuCourseRes->num_rows > 0 ? $stuCourseRes->fetch_assoc() : null;

// For timetable, fetch all entries for the latest student_course_id
$stuTimetable = [];
if ($stuCourse) {
    $timetableRes = $conn->query("SELECT * FROM student_timetable WHERE student_course_id = {$stuCourse['student_course_id']}");
    while ($row = $timetableRes->fetch_assoc()) {
        $stuTimetable[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $last_name = $conn->real_escape_string($_POST['Last_Name']);
        $first_name = $conn->real_escape_string($_POST['First_Name']);
        $gender = $conn->real_escape_string($_POST['Gender']);
        $dob = $conn->real_escape_string($_POST['DOB']);
        $school_syllabus = $conn->real_escape_string($_POST['School_Syllabus']);
        $school_intake = $conn->real_escape_string($_POST['School_Intake']);
        $current_grade = $conn->real_escape_string($_POST['Current_School_Grade']);
        $school = $conn->real_escape_string($_POST['School']);
        $mathology_level = $conn->real_escape_string($_POST['Mathology_Level']);
        $how_did_you_heard_about_us = $conn->real_escape_string($_POST['How_Did_You_Heard_About_Us']);
        $primary_contact = $conn->real_escape_string($_POST['primary_contact']);
        $secondary_contact = $conn->real_escape_string($_POST['secondary_contact']);

        $course_level = $conn->real_escape_string($_POST['course_level']);
        $course_id = intval($_POST['course_id']);
        $enrollment_date = $conn->real_escape_string($_POST['Enrollment_Date']);
        $day = $conn->real_escape_string($_POST['Day']);
        $start_time = $conn->real_escape_string($_POST['Start_Time']);
        $end_time = $conn->real_escape_string($_POST['End_Time']);

        // Update students table
        $updateStudentQuery = "UPDATE students SET
            Last_Name='$last_name',
            First_Name='$first_name',
            Gender='$gender',
            DOB='$dob',
            School_Syllabus='$school_syllabus',
            School_Intake='$school_intake',
            Current_School_Grade='$current_grade',
            School='$school',
            Mathology_Level='$mathology_level',
            How_Did_You_Heard_About_Us='$how_did_you_heard_about_us',
            primary_contact='$primary_contact',
            secondary_contact='$secondary_contact'
            WHERE student_id = $student_id";
        if (!$conn->query($updateStudentQuery)) {
            throw new Exception("Error updating student: " . $conn->error);
        }

        // Update/insert student_courses (only keep one for this UI)
        if ($stuCourse) {
            $updateCourseQuery = "UPDATE student_courses SET course_id='$course_id', enrollment_date='$enrollment_date' WHERE student_course_id={$stuCourse['student_course_id']}";
            if (!$conn->query($updateCourseQuery)) throw new Exception("Error updating student course: " . $conn->error);
            $student_course_id = $stuCourse['student_course_id'];
        } else {
            $insertCourseQuery = "INSERT INTO student_courses (student_id, course_id, enrollment_date)
                                  VALUES ('$student_id', '$course_id', '$enrollment_date')";
            if (!$conn->query($insertCourseQuery)) throw new Exception("Error adding student course: " . $conn->error);
            $student_course_id = $conn->insert_id;
        }

        // Timetable: remove old, add new (for this student_course_id)
        $conn->query("DELETE FROM student_timetable WHERE student_course_id = $student_course_id");
        $insertTimetableQuery = "INSERT INTO student_timetable (student_course_id, day, start_time, end_time, status)
                                 VALUES ('$student_course_id', '$day', '$start_time', '$end_time', 'active')";
        if (!$conn->query($insertTimetableQuery)) {
            throw new Exception("Error adding student timetable: " . $conn->error);
        }

        header("Location: ../Pages/admin/users.php?active_tab=students&message=Student+updated+successfully");
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/forms.css">
</head>
<body>
    <h1>Edit Student</h1>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form id="student-form" method="POST">
        <input type="hidden" name="user_type" value="student">
        <label for="student_last_name">Last Name:</label>
        <input type="text" id="student_last_name" name="Last_Name" value="<?=htmlspecialchars($student['Last_Name'])?>" required><br>
        <label for="student_first_name">First Name:</label>
        <input type="text" id="student_first_name" name="First_Name" value="<?=htmlspecialchars($student['First_Name'])?>" required><br>
        <label for="student_gender">Gender:</label>
        <select id="student_gender" name="Gender" required>
            <option value="1" <?=$student['Gender'] == '1' ? 'selected' : ''?>>Male</option>
            <option value="0" <?=$student['Gender'] == '0' ? 'selected' : ''?>>Female</option>
        </select><br>
        <label for="student_dob">Date of Birth:</label>
        <input type="date" id="student_dob" name="DOB" value="<?=htmlspecialchars($student['DOB'])?>" required><br>
        <label for="student_school_syllabus">School Syllabus:</label>
        <input type="text" id="student_school_syllabus" name="School_Syllabus" value="<?=htmlspecialchars($student['School_Syllabus'])?>"><br>
        <label for="student_school_intake">School Intake (Month):</label>
        <select id="student_school_intake" name="School_Intake" required>
            <?php
            $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            foreach ($months as $month):
            ?>
                <option value="<?=$month?>" <?=$student['School_Intake'] == $month ? 'selected' : ''?>><?=$month?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="student_current_grade">Current School Grade:</label>
        <input type="text" id="student_current_grade" name="Current_School_Grade" value="<?=htmlspecialchars($student['Current_School_Grade'])?>"><br>
        <label for="student_school">School:</label>
        <input type="text" id="student_school" name="School" value="<?=htmlspecialchars($student['School'])?>"><br>
        <label for="student_mathology_level">Mathology Level:</label>
        <input type="text" id="student_mathology_level" name="Mathology_Level" value="<?=htmlspecialchars($student['Mathology_Level'])?>"><br>
        <label for="How_Did_You_Heard_About_Us">How did you hear about us?</label>
        <input type="text" id="How_Did_You_Heard_About_Us" name="How_Did_You_Heard_About_Us" maxlength="100" value="<?=htmlspecialchars($student['How_Did_You_Heard_About_Us'])?>"><br>
        <label for="student_primary_contact">Primary Contact:</label>
        <input type="text" id="student_primary_contact" name="primary_contact" pattern="[0-9]{10,12}" maxlength="12" value="<?=htmlspecialchars($student['primary_contact'])?>" required><br>
        <label for="student_secondary_contact">Secondary Contact:</label>
        <input type="text" id="student_secondary_contact" name="secondary_contact" pattern="[0-9]{10,12}" maxlength="12" value="<?=htmlspecialchars($student['secondary_contact'])?>"><br>
        
        <!-- Course Level and Name selection -->
        <label for="student_course_level">Course Level:</label>
        <select id="student_course_level" name="course_level" required>
            <option value="">Select Level</option>
            <?php foreach ($levels as $level): ?>
                <option value="<?=$level?>" <?=($stuCourse && $courses[array_search($stuCourse['course_id'], array_column($courses, 'course_id'))]['level'] == $level) ? 'selected' : ''?>><?=$level?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="student_course_name">Course Name:</label>
        <select id="student_course_name" name="course_id" required>
            <option value="">Select Course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?=$course['course_id']?>"
                    data-level="<?=$course['level']?>"
                    <?=($stuCourse && $stuCourse['course_id'] == $course['course_id']) ? 'selected' : ''?>>
                    <?=$course['course_name']?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <label for="enrollment_date">Enrollment Date:</label>
        <input type="date" id="enrollment_date" name="Enrollment_Date" value="<?=htmlspecialchars($stuCourse ? $stuCourse['enrollment_date'] : '')?>" required><br>
        <label for="student_day">Day:</label>
        <select id="student_day" name="Day" required>
            <?php
            $stuDay = $stuTimetable && count($stuTimetable) > 0 ? $stuTimetable[0]['day'] : '';
            $daysOfWeek = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
            foreach ($daysOfWeek as $day):
            ?>
                <option value="<?=$day?>" <?=$stuDay == $day ? 'selected' : ''?>><?=$day?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="student_start_time">Start Time:</label>
        <input type="time" id="student_start_time" name="Start_Time" value="<?=htmlspecialchars($stuTimetable && count($stuTimetable) > 0 ? $stuTimetable[0]['start_time'] : '')?>" required><br>
        <label for="student_end_time">End Time:</label>
        <input type="time" id="student_end_time" name="End_Time" value="<?=htmlspecialchars($stuTimetable && count($stuTimetable) > 0 ? $stuTimetable[0]['end_time'] : '')?>" required><br><br>
        <button type="submit">Update Student</button>
        <a href="../Pages/admin/users.php">Cancel</a>
    </form>
<script>
const allCourses = <?=json_encode($courses)?>;

document.getElementById('student_course_level').addEventListener('change', function() {
    const selectedLevel = this.value;
    const nameSelect = document.getElementById('student_course_name');
    nameSelect.innerHTML = '<option value="">Select Course</option>';
    allCourses.forEach(function(course) {
        if (course.level === selectedLevel) {
            const opt = document.createElement('option');
            opt.value = course.course_id;
            opt.textContent = course.course_name;
            opt.setAttribute('data-level', course.level);
            nameSelect.appendChild(opt);
        }
    });
});
</script>
</body>
</html>