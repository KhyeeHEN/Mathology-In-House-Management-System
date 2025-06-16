document.addEventListener("DOMContentLoaded", () => {
    const ewalletDetails = document.getElementById("ewallet-details");
    const bankingDetails = document.getElementById("banking-details");
    const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
    const paymentForm = document.getElementById("payment_form");
    const modal = document.getElementById("payment-modal");
    const modalMsg = document.getElementById("modal-message");
    const closeModalBtn = document.getElementById("close-modal");

    // Show/hide details based on payment method
    paymentOptions.forEach(option => {
        option.addEventListener("change", () => {
            ewalletDetails.style.display = option.value === "ewallet" ? "block" : "none";
            bankingDetails.style.display = option.value === "onlinebanking" ? "block" : "none";
        });
    });

    // Handle form submit
    paymentForm.addEventListener("submit", function (e) {
        e.preventDefault(); // prevent default submission

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
            const formData = new FormData(paymentForm);

            fetch(paymentForm.action, {
                method: "POST",
                body: formData
            })
            .then(res => res.ok ? res.text() : Promise.reject("Server error"))
            .then(response => {
                showModal("✅ Payment submitted successfully!");
                paymentForm.reset();
                ewalletDetails.style.display = "none";
                bankingDetails.style.display = "none";
            })
            .catch(err => {
                showModal("❌ Submission failed. Please try again.");
            });
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


// function searchTable() {
// const input = document.getElementById("searchInput");
// const filter = input.value.toLowerCase();
// const table = document.querySelector(".payment-table");
// const rows = table.querySelectorAll("tbody tr");

// rows.forEach(row => {
//     const cells = row.querySelectorAll("td");
//     let matchFound = false;

//     cells.forEach(cell => {
//         if (cell.textContent.toLowerCase().includes(filter)) {
//             matchFound = true;
//         }
//     });

//     row.style.display = matchFound ? "" : "none";
//     });
// }

