<?php
include '../setting.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch list of instructors
$instructors_sql = "SELECT instructor_id, First_Name, Last_Name FROM instructor";
$instructors_result = $conn->query($instructors_sql);
$instructors = $instructors_result->fetch_all(MYSQLI_ASSOC);

// Handle instructor selection
$selected_instructor_id = $_GET['instructor_id'] ?? null;

// Fetch timetable for selected instructor
if ($selected_instructor_id) {
    $timetable_sql = "SELECT it.id, it.day, it.start_time, it.end_time, it.course 
                      FROM instructor_timetable it
                      JOIN instructor_courses ic ON it.instructor_course_id = ic.instructor_course_id
                      WHERE ic.instructor_id = ?";
    $timetable_stmt = $conn->prepare($timetable_sql);
    $timetable_stmt->bind_param("i", $selected_instructor_id);
    $timetable_stmt->execute();
    $timetable_result = $timetable_stmt->get_result();
    $timetable = $timetable_result->fetch_all(MYSQLI_ASSOC);
}

// Handle adding new timetable entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_timetable'])) {
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $instructor_course_id = $_POST['instructor_course_id'];

    $insert_sql = "INSERT INTO instructor_timetable (day, start_time, end_time, instructor_course_id, course) 
                   VALUES (?, ?, ?, ?, (SELECT course_name FROM courses WHERE course_id = (SELECT course_id FROM instructor_courses WHERE instructor_course_id = ?)))";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sssii", $day, $start_time, $end_time, $instructor_course_id, $instructor_course_id);
    if ($insert_stmt->execute()) {
        header("Location: admin_profile_with_timetable.php?instructor_id=$selected_instructor_id");
        exit();
    } else {
        echo "Error adding timetable entry: " . $insert_stmt->error;
    }
}

// Handle editing timetable entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_timetable'])) {
    $timetable_id = $_POST['timetable_id'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $instructor_course_id = $_POST['instructor_course_id'];

    $update_sql = "UPDATE instructor_timetable SET day = ?, start_time = ?, end_time = ?, instructor_course_id = ?, course = (SELECT course_name FROM courses WHERE course_id = (SELECT course_id FROM instructor_courses WHERE instructor_course_id = ?)) WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssiii", $day, $start_time, $end_time, $instructor_course_id, $instructor_course_id, $timetable_id);
    if ($update_stmt->execute()) {
        header("Location: admin_profile_with_timetable.php?instructor_id=$selected_instructor_id");
        exit();
    } else {
        echo "Error updating timetable entry: " . $update_stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/adminProfile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("../includes/Top_Nav_Bar_Admin.php"); ?>

            <!-- Content -->
            <div class="content">
                <h2>Instructor Timetable Management</h2>

                <!-- Instructor Selection -->
                <form method="GET" action="admin_profile_with_timetable.php">
                    <label for="instructor_id">Select Instructor:</label>
                    <select name="instructor_id" id="instructor_id">
                        <option value="">-- Select Instructor --</option>
                        <?php foreach ($instructors as $instructor): ?>
                            <option value="<?= $instructor['instructor_id'] ?>" <?= $selected_instructor_id == $instructor['instructor_id'] ? 'selected' : '' ?>>
                                <?= $instructor['First_Name'] . ' ' . $instructor['Last_Name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">View Timetable</button>
                </form>

                <?php if ($selected_instructor_id): ?>
                    <!-- Fetch courses for the instructor -->
                    <?php
                    $courses_sql = "SELECT ic.instructor_course_id, c.course_name 
                                    FROM instructor_courses ic
                                    JOIN courses c ON ic.course_id = c.course_id
                                    WHERE ic.instructor_id = ?";
                    $courses_stmt = $conn->prepare($courses_sql);
                    $courses_stmt->bind_param("i", $selected_instructor_id);
                    $courses_stmt->execute();
                    $courses_result = $courses_stmt->get_result();
                    $courses = $courses_result->fetch_all(MYSQLI_ASSOC);
                    ?>

                    <!-- Add New Timetable Entry -->
                    <h3>Add New Timetable Entry</h3>
                    <form method="POST">
                        <input type="hidden" name="add_timetable" value="1">
                        <label for="day">Day:</label>
                        <select name="day" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                        </select><br>
                        <label for="start_time">Start Time:</label>
                        <input type="time" name="start_time" required><br>
                        <label for="end_time">End Time:</label>
                        <input type="time" name="end_time" required><br>
                        <label for="instructor_course_id">Course:</label>
                        <select name="instructor_course_id" required>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['instructor_course_id'] ?>"><?= $course['course_name'] ?></option>
                            <?php endforeach; ?>
                        </select><br>
                        <button type="submit">Add Entry</button>
                    </form>

                    <!-- Display Timetable -->
                    <?php if (!empty($timetable)): ?>
                        <h3>Current Timetable</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Course</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timetable as $entry): ?>
                                    <tr>
                                        <td><?= $entry['day'] ?></td>
                                        <td><?= $entry['start_time'] ?></td>
                                        <td><?= $entry['end_time'] ?></td>
                                        <td><?= $entry['course'] ?></td>
                                        <td>
                                            <!-- Edit Form -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="edit_timetable" value="1">
                                                <input type="hidden" name="timetable_id" value="<?= $entry['id'] ?>">
                                                <select name="day" required>
                                                    <option value="Monday" <?= $entry['day'] == 'Monday' ? 'selected' : '' ?>>Monday</option>
                                                    <option value="Tuesday" <?= $entry['day'] == 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                                                    <option value="Wednesday" <?= $entry['day'] == 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                                                    <option value="Thursday" <?= $entry['day'] == 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                                                    <option value="Friday" <?= $entry['day'] == 'Friday' ? 'selected' : '' ?>>Friday</option>
                                                </select>
                                                <input type="time" name="start_time" value="<?= $entry['start_time'] ?>" required>
                                                <input type="time" name="end_time" value="<?= $entry['end_time'] ?>" required>
                                                <select name="instructor_course_id" required>
                                                    <?php foreach ($courses as $course): ?>
                                                        <option value="<?= $course['instructor_course_id'] ?>" <?= $entry['instructor_course_id'] == $course['instructor_course_id'] ? 'selected' : '' ?>>
                                                            <?= $course['course_name'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No timetable entries found for this instructor.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>