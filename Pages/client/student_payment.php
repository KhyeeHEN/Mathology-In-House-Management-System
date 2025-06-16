<?php
session_start();
include '../setting.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get the active course and its fee level
$sql = "
    SELECT sc.course_id, sc.is_new_student, c.course_name, c.level
    FROM student_courses sc
    JOIN courses c ON sc.course_id = c.course_id
    WHERE sc.student_id = ? AND sc.status = 'active'
    LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($course_id, $is_new, $course_name, $level);
$stmt->fetch();
$stmt->close();

// Default mode
$payment_mode = 'monthly';

// Get base fee
$fee_stmt = $conn->prepare("SELECT fee_amount FROM course_fees WHERE course_id = ? AND time = ?");
$fee_stmt->bind_param("is", $course_id, $payment_mode);
$fee_stmt->execute();
$fee_stmt->bind_result($base_fee);
$fee_stmt->fetch();
$fee_stmt->close();

// One-time fees
$one_time_fee = 0;
if ($is_new) {
    $fee_q = $conn->query("SELECT SUM(amount) AS total FROM one_time_fees");
    $one_time_fee = $fee_q->fetch_assoc()['total'];
}

$total = $base_fee + $one_time_fee;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Payment</title>
</head>
<body>
    <h2>Student Payment</h2>
    <form method="POST" action="process_student_payment.php">
        <p><strong>Course:</strong> <?php echo "$course_name ($level)"; ?></p>

        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        <input type="hidden" name="is_new" value="<?php echo $is_new; ?>">

        <label>Payment Mode:
            <select name="payment_mode" id="payment_mode">
                <option value="monthly" selected>Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="semi_annually">Semi-Annually</option>
            </select>
        </label>

        <p><strong>Base Fee:</strong> RM <span id="base_fee"><?php echo number_format($base_fee, 2); ?></span></p>
        <p><strong>One-Time Fees:</strong> RM <?php echo number_format($one_time_fee, 2); ?></p>

        <input type="hidden" name="base_fee" id="hidden_base_fee" value="<?php echo $base_fee; ?>">
        <input type="hidden" name="one_time_fee" value="<?php echo $one_time_fee; ?>">

        <label>Total Amount:</label>
        <input type="text" name="payment_amount" id="payment_amount" value="<?php echo number_format($total, 2); ?>" readonly>

        <label>Payment Method:
            <select name="payment_method" required>
                <option value="cash">Cash</option>
                <option value="credit_card">Credit Card</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>
        </label>

        <button type="submit">Submit Payment</button>
    </form>

<script>
document.getElementById('payment_mode').addEventListener('change', function () {
    const mode = this.value;
    const course_id = <?php echo $course_id; ?>;
    const one_time = <?php echo $one_time_fee; ?>;

    fetch(`get_fee_by_mode.php?course_id=${course_id}&mode=${mode}`)
        .then(res => res.json())
        .then(data => {
            const base = parseFloat(data.fee || 0);
            const total = base + one_time;
            document.getElementById('base_fee').innerText = base.toFixed(2);
            document.getElementById('payment_amount').value = total.toFixed(2);
            document.getElementById('hidden_base_fee').value = base.toFixed(2);
        });
});
</script>
</body>
</html>
