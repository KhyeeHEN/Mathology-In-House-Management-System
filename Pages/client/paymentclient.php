<?php
session_start();
include '../setting.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//Get student_id and name
$stmt = $conn->prepare("SELECT s.student_id, CONCAT(s.First_Name, ' ', s.Last_Name) AS full_name FROM users u JOIN students s ON u.student_id = s.student_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($student_id, $student_name);
$stmt->fetch();
$stmt->close();

//Get student's enrolled course
$sql = "
    SELECT sc.course_id, c.course_name, c.level
    FROM student_courses sc
    JOIN courses c ON sc.course_id = c.course_id
    WHERE sc.student_id = ? AND sc.status = 'active'
    LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($course_id, $course_name, $level);
$stmt->fetch();
$stmt->close();

//Get base fee for default mode (monthly)
$fee_stmt = $conn->prepare("SELECT time, fee_amount FROM course_fees WHERE course_id = ? LIMIT 1");
$fee_stmt->bind_param("i", $course_id);
$fee_stmt->execute();
$fee_stmt->bind_result($payment_mode, $base_fee);
$fee_stmt->fetch();
$fee_stmt->close();

//Check if this is the student's first payment
$paid_check = $conn->prepare("SELECT COUNT(*) FROM payment WHERE student_id = ?");
$paid_check->bind_param("i", $student_id);
$paid_check->execute();
$paid_check->bind_result($payment_count);
$paid_check->fetch();
$paid_check->close();
$has_paid = $payment_count > 0;

//Calculate one-time fee if first time
$one_time_fee = 0;
if (!$has_paid) {
    $result = $conn->query("SELECT SUM(amount) AS total FROM one_time_fees");
    $one_time_fee = $result->fetch_assoc()['total'];
}

$total = $base_fee + $one_time_fee;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Payment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="../../Styles/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- <script src="../../Scripts/payment.js" defer></script> -->
</head>

<body>
    <div class="dashboard-container">
        <?php include("../includes/Aside_Nav_Student.php"); ?>
        <main class="main-content">
            <?php include("../includes/Top_Nav_Bar_Student.php"); ?>

            <form id="payment_form" method="POST" action="../../sql/process_student_payment.php">
                <fieldset>
                    <h1>Student Payment</h1>

                    <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student_name); ?></p>
                    <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>">

                    <p><strong>Course:</strong> <?php echo "$course_name ($level)"; ?></p>
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <input type="hidden" name="is_first_payment" value="<?php echo $has_paid ? 0 : 1; ?>">

                    <p><strong>Base Fee:</strong> RM <span id="base_fee"><?php echo number_format($base_fee, 2); ?></span></p>
                    <p><strong>One-Time Fees:</strong> RM <?php echo number_format($one_time_fee, 2); ?></p>

                    <input type="hidden" name="base_fee" id="hidden_base_fee" value="<?php echo $base_fee; ?>">
                    <input type="hidden" name="one_time_fee" value="<?php echo $one_time_fee; ?>">

                    <p><strong>Payment Mode:</strong> <?php echo ucfirst($payment_mode); ?></p>
                    <input type="hidden" name="payment_mode" value="<?php echo $payment_mode; ?>">

                    <p><strong>Total Amount:</strong> RM <?php echo number_format($total, 2); ?></p>
                    <input type="hidden" name="payment_amount" value="<?php echo $total; ?>">
                </fieldset>

                <fieldset>
                    <legend>Select Payment Method</legend>
                    <label><input type="radio" name="payment_method" value="ewallet" required> E-Wallet</label>
                    <label><input type="radio" name="payment_method" value="bank_transfer"> Online Banking</label>
                </fieldset>

                <br>
                <input type="submit" value="Check Out" id="checkoutButton">
                <button type="button" id="cancelButton">Cancel</button>
            </form>
        </main>
    </div>

    <script type="module" src="/Scripts/common.js"></script>
    <script>
        document.getElementById('payment_mode').addEventListener('change', function() {
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
