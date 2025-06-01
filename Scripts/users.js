function showTable(tableId) {
    // Remove active class from all buttons
    document.querySelectorAll('.filter-buttons button').forEach(button => {
        button.classList.remove('active');
    });

    // Add active class to the clicked button
    event.target.classList.add('active');

    // Hide all tables
    document.querySelectorAll('.table-container').forEach(table => {
        table.classList.remove('active');
    });

    // Show the selected table
    document.getElementById(tableId).classList.add('active');

    // Update the hidden input field to reflect the current active tab
    const activeTabInput = document.getElementById('active_tab');
    if (activeTabInput) {
        if (tableId === 'students-table') {
            activeTabInput.value = 'students';
        } else if (tableId === 'instructors-table') {
            activeTabInput.value = 'instructors';
        } else if (tableId === 'admins-table') {
            activeTabInput.value = 'admins';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('active_tab') || 'students';

    // Determine the appropriate table and button IDs
    let tableId, buttonId;
    if (activeTab === 'instructors') {
        tableId = 'instructors-table';
        buttonId = 'instructors-btn';
    } else if (activeTab === 'admins') {
        tableId = 'admins-table';
        buttonId = 'admins-btn';
    } else {
        tableId = 'students-table';
        buttonId = 'students-btn';
    }

    // Highlight the active button
    document.querySelectorAll('.filter-buttons button').forEach(button => {
        button.classList.remove('active');
    });
    document.getElementById(buttonId).classList.add('active');

    // Show the correct table
    document.querySelectorAll('.table-container').forEach(table => {
        table.classList.remove('active');
    });
    document.getElementById(tableId).classList.add('active');

    // Reset button functionality
    const resetButton = document.getElementById('reset-button');
    // Remove previous reset listener if any, for clarity
    resetButton.addEventListener('click', () => {
        // Instead of submitting the form with empty search, reload page without query params
        window.location = 'users.php';
    });

    // Ensure pagination parameters reset to 1 on search
    searchForm.addEventListener('submit', () => {
        document.getElementById('students_page').value = '1'; // Reset to page 1
        document.getElementById('instructors_page').value = '1'; // Reset to page 1
        document.getElementById('admins_page').value = '1'; // Reset to page 1
    });
});

function setActiveTab(tab) {
    document.getElementById('active_tab').value = tab;
}