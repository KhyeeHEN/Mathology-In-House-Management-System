<?php
include '../setting.php';

$message = isset($_GET['message']) ? $_GET['message'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'payment_date';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="../../Styles/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar_Admin.php"); ?>

            <?php if ($message): ?>
                <div style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
                <br>
                <br>
                <br>
                <br>
            <!-- Search + Sort -->
            <div class="search-bar">
                <form method="GET" action="payment.php">
                    <input type="text" name="search" placeholder="Search payments..." value="<?php echo htmlspecialchars($search); ?>">

                    <label for="sort">Sort by:</label>
                    <select id="sort" name="sort">
                        <option value="payment_id" <?php if ($sort === 'payment_id') echo 'selected'; ?>>Payment ID</option>
                        <option value="student_name" <?php if ($sort === 'student_name') echo 'selected'; ?>>Student</option>
                        <option value="payment_status" <?php if ($sort === 'payment_status') echo 'selected'; ?>>Status</option>
                        <option value="payment_amount" <?php if ($sort === 'payment_amount') echo 'selected'; ?>>Amount</option>
                    </select>

                    <select id="direction" name="direction">
                        <option value="ASC" <?php if ($direction === 'ASC') echo 'selected'; ?>>Ascending</option>
                        <option value="DESC" <?php if ($direction === 'DESC') echo 'selected'; ?>>Descending</option>
                    </select>

                    <button type="submit"><i class="fas fa-search"></i></button>
                    <button type="button" onclick="window.location='payment.php'"><i class="fas fa-undo"></i></button>
                    <button type="button" onclick="window.location='manage_fees.php'"><i class="fas fa-book"></i></button>
                    <button type="button" onclick="window.location='../../sql/add_payment.php'"><i class="fas fa-money-bill"></button>
                </form>
            </div>

            <!-- Payment Table -->
            <div class="table-container">
                <?php include '../../sql/payment_data.php'; ?>
            </div>
        </main>
    </div>

    <script src="/Scripts/payment.js?v=<?php echo time(); ?>"></script>
    <script type="module" src="/Scripts/common.js"></script>
    <script>
function toggleDetails(id) {
    const row = document.getElementById(id);
    if (row.style.display === 'none') {
        row.style.display = 'table-row';
    } else {
        row.style.display = 'none';
    }
}
</script>
</body>
</html>
