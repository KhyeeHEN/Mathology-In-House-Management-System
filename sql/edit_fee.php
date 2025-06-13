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
        /* Your existing styles... */
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

        <label>Time:
            <input type="text" name="time" required
                value="<?= htmlspecialchars($course['time']) ?>">
        </label>

        <button type="submit">Update Fee</button>
        <a href="../Pages/admin/manage_fees.php">Cancel</a>
    </form>
</body>
</html>
