document.addEventListener("DOMContentLoaded", () => {
    const ewalletDetails = document.getElementById("ewallet-details");
    const bankingDetails = document.getElementById("banking-details");
    const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
    const paymentForm = document.getElementById("payment_form");
    const modal = document.getElementById("payment-modal");
    const modalMsg = document.getElementById("modal-message");
    const closeModalBtn = document.getElementById("close-modal");

    paymentOptions.forEach(option => {
        option.addEventListener("change", () => {
            if (option.value === "ewallet") {
                ewalletDetails.style.display = "block";
                bankingDetails.style.display = "none";
            } else if (option.value === "onlinebanking") {
                bankingDetails.style.display = "block";
                ewalletDetails.style.display = "none";
            }
        });
    });

    paymentForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!selectedMethod) {
            showModal("⚠️ Please select a payment method.");
            return;
        }

        let valid = true;

        if (selectedMethod.value === "ewallet") {
            const provider = document.getElementById("ewallet_provider").value;
            const number = document.getElementById("ewallet_number").value.trim();
            if (!provider || !number) {
                showModal("⚠️ Please fill in all E-Wallet details.");
                valid = false;
            }
        } else if (selectedMethod.value === "onlinebanking") {
            const bank = document.getElementById("bank_name").value;
            const txnId = document.getElementById("transaction_id").value.trim();
            if (!bank || !txnId) {
                showModal("⚠️ Please fill in all Online Banking details.");
                valid = false;
            }
        }

        if (valid) {
            const isSuccess = Math.random() > 0.2;
            if (isSuccess) {
                showModal("✅ Payment successful! Thank you.");
                paymentForm.reset();
                ewalletDetails.style.display = "none";
                bankingDetails.style.display = "none";
            } else {
                showModal("❌ Payment failed. Please try again.");
            }
        }
    });

    closeModalBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    function showModal(message) {
        modalMsg.textContent = message;
        modal.style.display = "block";
    }
});
