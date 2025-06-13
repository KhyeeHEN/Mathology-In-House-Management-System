<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['id'])) {
    die("Course ID is required.");
}

$course_id = intval($_GET['id']);

$sql = "
    SELECT c.course_id, c.course_name, c.level, f.fee_amount, f.package_hours, f.time
    FROM courses c
    LEFT JOIN course_fees f ON c.course_id = f.course_id
    WHERE c.course_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Course not found.");
}

$course = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Course Fee</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            padding: 40px;
        }

        form {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        h2 {
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input[type="number"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        button {
            background-color: #1f2937;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
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

        a:hover {
            color: rgb(71, 82, 95);
        }
    </style>
</head>

<body>
    <form action="../sql/update_fee.php" method="POST">
        <h2>Edit Course Fee - <?= htmlspecialchars($course['course_name']) ?></h2>

        <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">

        <p><strong>Level:</strong> <?= htmlspecialchars($course['level']) ?></p>

        <label>Fee Amount (RM):
            <input type="number" name="fee_amount" step="0.01" min="0" required
                value="<?= htmlspecialchars($course['fee_amount'] ?? 0.00) ?>">
        </label>

        <label>Package Hours:
            <input type="number" name="package_hours" step="1" min="0" required
                value="<?= htmlspecialchars($course['package_hours'] ?? 0) ?>">
        </label>

        <label>Time:
            <input type="text" name="time" required
                value="<?= htmlspecialchars($course['time'] ?? '') ?>">
        </label>

        <button type="submit">Update Fee</button>
        <a href="../Pages/admin/manage_fees.php">Cancel</a>
        <a href="../Pages/admin/manage_fees.php">Cancel</a>
    </form>
</body>

</html>
