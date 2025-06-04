<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="../../styles/common.css">
    <link rel="stylesheet" href="../../styles/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../../scripts/payment.js"></script>
</head>

<body>
    <div class="dashboard-container">
      <?php include("../includes/Aside_Nav_Student.php"); ?>
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
          <?php include("../includes/Top_Nav_Bar_Student.php"); ?>

        <form id="payment_form"  method="post" novalidate="novalidate">
            <fieldset>
                <h1>Payment</h1>
                <p>Student Name:<span id="fname"></span></p>
                <input type="hidden" name="fname" id="fname">

                <p>Year:<span id="year"></span></p>
                <input type="hidden" name="year" id="year">

                <p>Mathology Level:<span id="level"></span></p>
                <input type="hidden" name="level" id="level">

                <p>Amount: RM<span id="confirm_cost"></span></p>
                <input type="hidden" name="cost" id="cost">
            </fieldset>

            <fieldset>
                <legend>Select Payment Method</legend>
                <label>
                    <input type="radio" name="payment_method" value="ewallet" required>
                    E-Wallet
                </label>
                <label>
                    <input type="radio" name="payment_method" value="onlinebanking">
                    Online Banking
                </label>
            </fieldset>

            <!-- E-Wallet Details -->
            <div id="ewallet-details" style="display: none;">
                <label for="ewallet_provider">E-Wallet Provider</label>
                <select name="ewallet_provider" id="ewallet_provider">
                    <option value="Touch 'n Go">Touch 'n Go</option>
                    <option value="Boost">Boost</option>
                    <option value="GrabPay">GrabPay</option>
                </select>
                <label for="ewallet_number">E-Wallet Account Number</label>
                <input type="text" name="ewallet_number" id="ewallet_number">
            </div>

            <!-- Online Banking Details -->
            <div id="banking-details" style="display: none;">
                <label for="bank_name">Bank</label>
                <select name="bank_name" id="bank_name">
                    <option value="Maybank">Maybank</option>
                    <option value="CIMB">CIMB</option>
                    <option value="Public Bank">Public Bank</option>
                    <option value="RHB">RHB</option>
                </select>
                <label for="transaction_id">Transaction ID</label>
                <input type="text" name="transaction_id" id="transaction_id">
            </div>


            <br>
            <input type="submit" value="Check Out" name="submitButton" id="checkoutButton">
            <button type="button" id="cancelButton">Cancel</button>
        </form>
        <!-- Payment Modal -->
        <div id="payment-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:999;">
            <div style="background:white; width:90%; max-width:400px; margin:10% auto; padding:20px; border-radius:8px; text-align:center;">
                <p id="modal-message" style="margin-bottom:1rem; font-size:1.1rem;"></p>
                <button id="close-modal" style="padding:0.5rem 1rem; background:#4f46e5; color:white; border:none; border-radius:4px;">Close</button>
            </div>
        </div>


        </main>
    </div>
    <script type="module" src="../../scripts/common.js"></script>

</body>
</html>
