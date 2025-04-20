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
// Automatically set the active table based on the "active_tab" hidden input field
document.addEventListener('DOMContentLoaded', () => {
    const activeTabInput = document.getElementById('active_tab');
    if (activeTabInput) {
        const activeTabValue = activeTabInput.value;
        const tableId = activeTabValue === 'instructors' ? 'instructors-table' : 'students-table';

        // Simulate a button click to show the correct table
        const button = document.querySelector(
            activeTabValue === 'instructors' ? '#instructors-btn' : '#students-btn'
        );
        if (button) {
            button.click();
        }

        // Ensure the correct table is displayed
        document.querySelectorAll('.table-container').forEach(table => {
            table.classList.remove('active');
        });
        document.getElementById(tableId).classList.add('active');
    }
});