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
        activeTabInput.value = tableId === 'students-table' ? 'students' : 'instructors';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('active_tab') || 'students';

    // Activate the appropriate tab and table
    const tableId = activeTab === 'instructors' ? 'instructors-table' : 'students-table';
    const buttonId = activeTab === 'instructors' ? 'instructors-btn' : 'students-btn';

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
});

function setActiveTab(tab) {
    document.getElementById('active_tab').value = tab;
}