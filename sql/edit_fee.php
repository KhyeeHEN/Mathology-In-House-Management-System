<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['id'])) {
    die("Fee ID is required.");
}

$fee_id = intval($_GET['id']);

$sql = "
    SELECT f.fee_id, c.course_id, c.course_name, c.level, f.fee_amount, f.package_hours, f.time
    FROM course_fees f
    JOIN courses c ON c.course_id = f.course_id
    WHERE f.fee_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $fee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Course fee not found.");
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
            color: #1f2937;
            margin: 0;
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #1f2937;
        }

        form {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #1f2937;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: box-shadow 0.3s ease;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #1f2937;
            box-shadow: 0 0 5px rgba(31, 41, 55, 0.5);
        }

        p {
            margin-bottom: 10px;
        }

        button {
            background-color: #1f2937;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: rgb(71, 82, 95);
        }

        a {
            margin-left: 15px;
            text-decoration: none;
            color: #1f2937;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        a:hover {
            color: rgb(71, 82, 95);
        }
    </style>
</head>
<body>
    <form action="../sql/update_fee.php" method="POST">
        <h2>Edit Course Fee - <?= htmlspecialchars($course['course_name']) ?></h2>

        <input type="hidden" name="fee_id" value="<?= $course['fee_id'] ?>">

        <p><strong>Level:</strong> <?= htmlspecialchars($course['level']) ?></p>

        <label>Fee Amount (RM):
            <input type="number" name="fee_amount" step="0.01" min="0" required
                value="<?= htmlspecialchars($course['fee_amount']) ?>">
        </label>

        <label>Package Hours:
            <input type="number" name="package_hours" step="1" min="0" required
                value="<?= htmlspecialchars($course['package_hours']) ?>">
        </label>

        <label>Time (e.g., Monthly, Quarterly):
            <input type="text" name="time" required
                value="<?= htmlspecialchars($course['time']) ?>">
        </label>

        <button type="submit">Update Fee</button>
        <a href="../Pages/admin/manage_fees.php">Cancel</a>
    </form>
</body>
</html>
