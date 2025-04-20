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
}