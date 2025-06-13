<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Course with Fee</title>
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
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        h2 {
            text-align: center;
            color: #1f2937;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
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
            margin-left: 10px;
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
    <form action="insert_course_with_fee.php" method="POST">
        <h2>Add New Course and Initial Fee</h2>

        <label>Course Name:</label>
        <input type="text" name="course_name" required>

        <label>Level:</label>
        <input type="text" name="level" required>

        <label>Fee Amount (RM):</label>
        <input type="number" name="fee_amount" step="0.01" min="0" required>

        <label>Package Hours:</label>
        <input type="number" name="package_hours" min="0" required>

        <label>Time (e.g. Monthly, Quarterly):</label>
        <input type="text" name="time" required>

        <button type="submit">Add Course + Fee</button>
        <a href="../Pages/admin/manage_fees.php">Cancel</a>
    </form>
</body>
</html>
