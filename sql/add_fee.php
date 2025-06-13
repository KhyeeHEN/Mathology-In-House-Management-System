<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Fetch all courses for the dropdown
$courses = $conn->query("SELECT course_id, course_name, level FROM courses ORDER BY course_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Course Fee</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            padding: 40px;
        }
        form {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            font-weight: bold;
            display: block;
            margin: 10px 0 5px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 20px;
        }
        button {
            background-color: #1f2937;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: rgb(71, 82, 95);
        }
        a {
            margin-left: 15px;
            text-decoration: none;
            font-weight: bold;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <form action="insert_fee.php" method="POST">
        <h2>Add New Course Fee</h2>

        <label for="course_id">Select Course:</label>
        <select name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php while ($row = $courses->fetch_assoc()): ?>
                <option value="<?= $row['course_id'] ?>">
                    <?= htmlspecialchars($row['course_name']) ?> (<?= htmlspecialchars($row['level']) ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label for="fee_amount">Fee Amount (RM):</label>
        <input type="number" name="fee_amount" step="0.01" min="0" required>

        <label for="package_hours">Package Hours:</label>
        <input type="number" name="package_hours" min="0" required>

        <label for="time">Time (e.g., Monthly, Quarterly):</label>
        <input type="text" name="time" required>

        <button type="submit">Add Fee</button>
        <a href="../Pages/admin/manage_fees.php">Cancel</a>
    </form>
</body>
</html>
