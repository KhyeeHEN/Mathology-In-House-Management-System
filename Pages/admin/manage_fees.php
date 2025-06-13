<?php
session_start();
require_once '../setting.php'; // update path as needed

if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized access");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'], $_POST['fee_amount'])) {
    $course_id = intval($_POST['course_id']);
    $fee_amount = floatval($_POST['fee_amount']);

    // Insert or update
    $stmt = $conn->prepare("
        INSERT INTO course_fees (course_id, fee_amount)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE fee_amount = VALUES(fee_amount)
    ");
    $stmt->bind_param("id", $course_id, $fee_amount);
    $stmt->execute();
    $message = "Fee updated successfully.";
}

// Fetch courses and fees
$sql = "
    SELECT c.course_id, c.course_name, c.level, f.fee_amount
    FROM courses c
    LEFT JOIN course_fees f ON c.course_id = f.course_id
    ORDER BY c.course_name
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Course Fees</title>
  <style>
    body { font-family: Arial; background: #f4f4f4; padding: 30px; }
    h1 { text-align: center; }
    table { width: 100%; background: white; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
    form { margin: 0; display: flex; gap: 10px; align-items: center; }
    input[type="number"] { width: 100px; padding: 5px; }
    button { padding: 6px 12px; background: #1f2937; color: white; border: none; cursor: pointer; }
    button:hover { background: #374151; }
    .message { color: green; text-align: center; font-weight: bold; }
  </style>
</head>
<body>
  <h1>Manage Course Fees</h1>
  <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

  <table>
    <thead>
      <tr>
        <th>Course Name</th>
        <th>Level</th>
        <th>Current Fee (RM)</th>
        <th>Update Fee</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['course_name']) ?></td>
          <td><?= htmlspecialchars($row['level']) ?></td>
          <td><?= number_format($row['fee_amount'] ?? 0, 2) ?></td>
          <td>
            <form method="POST">
              <input type="hidden" name="course_id" value="<?= $row['course_id'] ?>">
              <input type="number" name="fee_amount" step="0.01" min="0" required
                     value="<?= htmlspecialchars($row['fee_amount'] ?? '') ?>">
              <button type="submit">Update</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>
