<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['instructor_id'])) {
    header("Location: ../Pages/admin/users.php?active_tab=instructors");
    exit();
}

$instructor_id = intval($_GET['instructor_id']);
$error = null;

// Fetch instructor data
$instructorRes = $conn->query("SELECT * FROM instructor WHERE instructor_id = $instructor_id");
if (!$instructorRes || $instructorRes->num_rows === 0) {
    header("Location: ../Pages/admin/users.php?active_tab=instructors");
    exit();
}
$instructor = $instructorRes->fetch_assoc();

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

// Fetch current instructor course and timetable (assuming one course per instructor for this UI)
$instCourseRes = $conn->query("SELECT * FROM instructor_courses WHERE instructor_id = $instructor_id ORDER BY assigned_date DESC LIMIT 1");
$instCourse = $instCourseRes && $instCourseRes->num_rows > 0 ? $instCourseRes->fetch_assoc() : null;

// For timetable, fetch all entries for the latest instructor_course_id
$instTimetable = [];
if ($instCourse) {
    $timetableRes = $conn->query("SELECT * FROM instructor_timetable WHERE instructor_course_id = {$instCourse['instructor_course_id']}");
    while ($row = $timetableRes->fetch_assoc()) {
        $instTimetable[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $last_name = $conn->real_escape_string($_POST['Last_Name']);
        $first_name = $conn->real_escape_string($_POST['First_Name']);
        $gender = $conn->real_escape_string($_POST['Gender']);
        $dob = $conn->real_escape_string($_POST['DOB']);
        $highest_education = $conn->real_escape_string($_POST['Highest_Education']);
        $remark = $conn->real_escape_string($_POST['Remark']);
        $training_status = $conn->real_escape_string($_POST['Training_Status']);
        $employment_type = $conn->real_escape_string($_POST['Employment_Type']);
        $contact = $conn->real_escape_string($_POST['contact']);
        $hiring_status = $conn->real_escape_string($_POST['hiring_status']);
        $total_hours = floatval($_POST['Total_Hours']);

        $course_level = $conn->real_escape_string($_POST['course_level']);
        $course_id = intval($_POST['course_id']);

        $working_days = null;
        if ($employment_type === 'Part-Time' && isset($_POST['Working_Days'])) {
            $working_days_arr = array_map([$conn, 'real_escape_string'], (array)$_POST['Working_Days']);
            $working_days = implode(',', $working_days_arr);
        }

        // Update instructor table
        $updateInstructorQuery = "UPDATE instructor SET
            Last_Name='$last_name',
            First_Name='$first_name',
            Gender='$gender',
            DOB='$dob',
            Highest_Education='$highest_education',
            Remark='$remark',
            Training_Status='$training_status',
            Employment_Type='$employment_type',
            Working_Days=" . ($working_days ? "'$working_days'" : "NULL") . ",
            contact='$contact',
            hiring_status='$hiring_status',
            Total_Hours=$total_hours
            WHERE instructor_id = $instructor_id";
        if (!$conn->query($updateInstructorQuery)) {
            throw new Exception("Error updating instructor: " . $conn->error);
        }

        // Update/insert instructor_courses (only keep one for this UI)
        if ($instCourse) {
            $updateCourseQuery = "UPDATE instructor_courses SET course_id='$course_id' WHERE instructor_course_id={$instCourse['instructor_course_id']}";
            if (!$conn->query($updateCourseQuery)) throw new Exception("Error updating instructor course: " . $conn->error);
            $instructor_course_id = $instCourse['instructor_course_id'];
        } else {
            $assigned_date = date('Y-m-d');
            $insertCourseQuery = "INSERT INTO instructor_courses (instructor_id, course_id, assigned_date)
                                  VALUES ('$instructor_id', '$course_id', '$assigned_date')";
            if (!$conn->query($insertCourseQuery)) throw new Exception("Error adding instructor course: " . $conn->error);
            $instructor_course_id = $conn->insert_id;
        }

        // Timetable: remove old, add new (for this instructor_course_id)
        $conn->query("DELETE FROM instructor_timetable WHERE instructor_course_id = $instructor_course_id");
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
            // Full-Time: insert blank
            $insertTimetableQuery = "INSERT INTO instructor_timetable (day, start_time, end_time, status, instructor_course_id, course)
                VALUES ('', '00:00:00', '00:00:00', 'active', '$instructor_course_id', '')";
            if (!$conn->query($insertTimetableQuery)) {
                throw new Exception("Error adding blank instructor timetable: " . $conn->error);
            }
        }

        header("Location: ../Pages/admin/users.php?active_tab=instructors&message=Instructor+updated+successfully");
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
    <title>Edit Instructor</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/forms.css">
</head>
<body>
    <h1>Edit Instructor</h1>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form id="instructor-form" method="POST">
        <div class="form-row">
            <label for="instructor_last_name">Last Name:</label>
            <input type="text" id="instructor_last_name" name="Last_Name" value="<?=htmlspecialchars($instructor['Last_Name'])?>" required>
            <label for="instructor_first_name">First Name:</label>
            <input type="text" id="instructor_first_name" name="First_Name" value="<?=htmlspecialchars($instructor['First_Name'])?>" required>
            <label for="instructor_gender">Gender:</label>
            <select id="instructor_gender" name="Gender" required>
                <option value="1" <?=$instructor['Gender'] == '1' ? 'selected' : ''?>>Male</option>
                <option value="0" <?=$instructor['Gender'] == '0' ? 'selected' : ''?>>Female</option>
            </select>
            <label for="instructor_dob">Date of Birth:</label>
            <input type="date" id="instructor_dob" name="DOB" value="<?=htmlspecialchars($instructor['DOB'])?>" required>
        </div>
        <div class="form-row">
            <label for="instructor_highest_education">Highest Education:</label>
            <input type="text" id="instructor_highest_education" name="Highest_Education" value="<?=htmlspecialchars($instructor['Highest_Education'])?>">
            <label for="instructor_remark">Remark:</label>
            <textarea id="instructor_remark" name="Remark" style="min-width:140px;flex:1 1 140px;"><?=htmlspecialchars($instructor['Remark'])?></textarea>
            <label for="instructor_training_status">Training Status:</label>
            <input type="text" id="instructor_training_status" name="Training_Status" value="<?=htmlspecialchars($instructor['Training_Status'])?>">
        </div>
        <div class="form-row">
            <label for="instructor_contact">Contact Number:</label>
            <input type="text" id="instructor_contact" name="contact" pattern="[0-9]{10,12}" maxlength="12" value="<?=htmlspecialchars($instructor['contact'])?>" required>
            <label for="instructor_hiring_status">Hiring Status:</label>
            <select id="instructor_hiring_status" name="hiring_status" required>
                <option value="true" <?=$instructor['hiring_status'] == 'true' ? 'selected' : ''?>>Hired</option>
                <option value="false" <?=$instructor['hiring_status'] == 'false' ? 'selected' : ''?>>Not Hired</option>
            </select>
            <label for="instructor_total_hours">Total Hours:</label>
            <input type="number" id="instructor_total_hours" name="Total_Hours" step="0.1" min="0" value="<?=htmlspecialchars($instructor['Total_Hours'])?>" required>
            <label for="instructor_employment_type">Employment Type:</label>
            <select id="instructor_employment_type" name="Employment_Type" required>
                <option value="Full-Time" <?=$instructor['Employment_Type'] == 'Full-Time' ? 'selected' : ''?>>Full-Time</option>
                <option value="Part-Time" <?=$instructor['Employment_Type'] == 'Part-Time' ? 'selected' : ''?>>Part-Time</option>
            </select>
        </div>
        <div class="form-row">
            <label for="instructor_course_level">Course Level:</label>
            <select id="instructor_course_level" name="course_level" required>
                <option value="">Select Level</option>
                <?php foreach ($levels as $level): ?>
                    <option value="<?=$level?>" <?=($instCourse && $courses[array_search($instCourse['course_id'], array_column($courses, 'course_id'))]['level'] == $level) ? 'selected' : ''?>><?=$level?></option>
                <?php endforeach; ?>
            </select>
            <label for="instructor_course_name">Course Name:</label>
            <select id="instructor_course_name" name="course_id" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?=$course['course_id']?>"
                        data-level="<?=$course['level']?>"
                        <?=($instCourse && $instCourse['course_id'] == $course['course_id']) ? 'selected' : ''?>>
                        <?=$course['course_name']?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="part-time-days-times" style="display:<?=$instructor['Employment_Type'] == 'Part-Time' ? '' : 'none'?>">
            <div class="form-row">
                <label for="instructor_working_days">Working Days:</label>
                <select id="instructor_working_days" name="Working_Days[]" multiple <?=$instructor['Employment_Type'] == 'Full-Time' ? 'disabled' : ''?>>
                    <?php
                    $selected_days = $instructor['Working_Days'] ? explode(',', $instructor['Working_Days']) : [];
                    $daysOfWeek = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                    foreach ($daysOfWeek as $day): ?>
                        <option value="<?=$day?>" <?=in_array($day, $selected_days) ? 'selected' : ''?>><?=$day?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="day-times-container">
                <?php
                $timetableDays = [];
                foreach ($instTimetable as $row) {
                    if ($row['day']) $timetableDays[$row['day']] = $row;
                }
                foreach ($selected_days as $day):
                    $start = isset($timetableDays[$day]) ? $timetableDays[$day]['start_time'] : '';
                    $end = isset($timetableDays[$day]) ? $timetableDays[$day]['end_time'] : '';
                ?>
                <div>
                    <label><?=$day?> Start:</label>
                    <input type="time" name="start_time[<?=$day?>]" value="<?=$start?>" required>
                    <label>End:</label>
                    <input type="time" name="end_time[<?=$day?>]" value="<?=$end?>" required>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit">Update Instructor</button>
        <a href="../Pages/admin/users.php">Cancel</a>
    </form>
    <script>
        const allCourses = <?=json_encode($courses)?>;

        document.getElementById('instructor_course_level').addEventListener('change', function() {
            const selectedLevel = this.value;
            const nameSelect = document.getElementById('instructor_course_name');
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

        function setWorkingDaysState() {
            const employmentType = document.getElementById('instructor_employment_type');
            const daysDiv = document.getElementById('part-time-days-times');
            const workingDaysInput = document.getElementById('instructor_working_days');
            const dayTimesContainer = document.getElementById('day-times-container');
            if (employmentType.value === 'Part-Time') {
                daysDiv.style.display = '';
                workingDaysInput.disabled = false;
                updateDayTimes();
            } else {
                daysDiv.style.display = 'none';
                workingDaysInput.disabled = true;
                dayTimesContainer.innerHTML = '';
                // Deselect all
                for(let i=0;i<workingDaysInput.options.length;i++) {
                    workingDaysInput.options[i].selected = false;
                }
            }
        }
        document.getElementById('instructor_employment_type').addEventListener('change', setWorkingDaysState);

        function updateDayTimes() {
            const workingDaysInput = document.getElementById('instructor_working_days');
            const dayTimesContainer = document.getElementById('day-times-container');
            dayTimesContainer.innerHTML = '';
            Array.from(workingDaysInput.selectedOptions).forEach(option => {
                const day = option.value;
                // fetch old values if present
                let start = '', end = '';
                <?php if (!empty($timetableDays)) { ?>
                    const timetableDays = <?=json_encode($timetableDays)?>;
                    if (typeof timetableDays[day] !== 'undefined') {
                        start = timetableDays[day].start_time;
                        end = timetableDays[day].end_time;
                    }
                <?php } ?>
                dayTimesContainer.innerHTML += `
                    <div>
                        <label>${day} Start:</label>
                        <input type="time" name="start_time[${day}]" value="${start}" required>
                        <label>End:</label>
                        <input type="time" name="end_time[${day}]" value="${end}" required>
                    </div>
                `;
            });
        }
        document.getElementById('instructor_working_days').addEventListener('change', updateDayTimes);

        document.addEventListener('DOMContentLoaded', function() {
            setWorkingDaysState();
        });
    </script>
</body>
</html>