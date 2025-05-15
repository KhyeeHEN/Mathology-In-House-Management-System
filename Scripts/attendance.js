function searchTable() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();
    const table = document.querySelector(".attendence");
    const rows = table.querySelectorAll("tbody tr");

    rows.forEach(row => {
        const cells = row.querySelectorAll("td");
        let matchFound = false;

        cells.forEach(cell => {
            if (cell.textContent.toLowerCase().includes(filter)) {
                matchFound = true;
            }
        });

        row.style.display = matchFound ? "" : "none";
    });
}
